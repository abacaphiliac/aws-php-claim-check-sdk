<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Sqs;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\RuntimeException;
use Aws\Result;
use Aws\Sqs\SqsClient;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\StreamInterface;

class SqsExtendedClient extends SqsClient
{
    /** @var  SqsClient */
    private $sqsClient;
    
    /** @var  SqsExtendedClientConfiguration */
    private $configuration;

    /**
     * SqsExtendedClient constructor.
     * @param SqsClient $sqsClient
     * @param SqsExtendedClientConfiguration $configuration
     */
    public function __construct(SqsClient $sqsClient, SqsExtendedClientConfiguration $configuration)
    {
        $this->sqsClient = $sqsClient;
        $this->configuration = $configuration;
    }

    /**
     * @param string $name
     * @param mixed[] $args
     * @return Result|Promise
     * @throws ExceptionInterface
     */
    public function __call($name, array $args)
    {
        $params = array_key_exists(0, $args) ? $args[0] : [];
        
        switch ($name) {
            case 'sendMessage':
                $result = $this->sendSqsMessage($params);
                break;
            case 'receiveMessage':
                $result = $this->receiveSqsMessage($params);
                break;
            case 'deleteMessage':
                $result = $this->deleteSqsMessage($params);
                break;
            default:
                $result = $this->sqsClient->{$name}($params);
                break;
        }
        
        return $result;
    }

    /**
     * @param mixed[] $args
     * @return Result
     * @throws ExceptionInterface
     */
    private function sendSqsMessage(array $args = [])
    {
        $claimCheckSerializer = $this->configuration->getClaimCheckSerializer();

        $message = array_key_exists('MessageBody', $args) ? $args['MessageBody'] : '';

        $claimCheck = $this->storeMessageInS3($message);

        $args['MessageBody'] = $claimCheckSerializer->serialize($claimCheck);

        return $this->sqsClient->sendMessage($args);
    }

    /**
     * @param mixed[] $args
     * @return Result
     * @throws ExceptionInterface
     */
    private function receiveSqsMessage(array $args = [])
    {
        $result = $this->sqsClient->receiveMessage($args);

        $messages = array();

        foreach ($result->search('Messages[]') as $i => $message) {
            $messages[$i] = $this->decodeSqsMessage($message);
        }

        $result->offsetSet('Messages', $messages);
        
        return $result;
    }

    /**
     * @param mixed[] $args
     * @return Result
     * @throws ExceptionInterface
     */
    private function deleteSqsMessage(array $args = [])
    {
        if (array_key_exists('ReceiptHandle', $args) && $this->configuration->getDeleteFromS3()) {
            // Split receipt handle into S3 and SQS information.
            $decodedReceiptHandle = json_decode($args['ReceiptHandle'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $s3Client = $this->configuration->getS3Client();
                
                // Delete from S3.
                $s3Client->deleteObject(array(
                    'Bucket' => $decodedReceiptHandle['s3_bucket_name'],
                    'Key' => $decodedReceiptHandle['s3_key'],
                ));
                
                // Adjust SQS args.
                $args['ReceiptHandle'] = $decodedReceiptHandle['original_receipt_handle'];
            }
        }
        
        return $this->sqsClient->deleteMessage($args);
    }

    /**
     * @param mixed[] $message
     * @return string|bool
     * @throws ExceptionInterface
     */
    private function decodeSqsMessage(array $message)
    {
        if (!array_key_exists('Body', $message)) {
            // Unknown message body. Skip processing.
            return $message;
        }

        try {
            $claimCheck = $this->configuration->getClaimCheckSerializer()->unserialize($message['Body']);
        } catch (ExceptionInterface $e) {
            // Unknown message body. Skip processing.
            return $message;
        }
        
        if (!$claimCheck instanceof ClaimCheck) {
            // Unknown message body. Skip processing.
            return $message;
        }

        try {
            $message['Body'] = $this->fetchClaimCheckFromS3($claimCheck);
        } catch (ExceptionInterface $e) {
            // Unknown message body. Skip processing.
            return $message;
        }
        
        if (array_key_exists('ReceiptHandle', $message) && $this->configuration->getDeleteFromS3()) {
            // Prepend S3 information to receipt handle.
            $message['ReceiptHandle'] = $this->embedS3PointerInReceiptHandle(
                $message['ReceiptHandle'],
                $claimCheck->getS3BucketName(),
                $claimCheck->getS3Key()
            );
        }
        
        return $message;
    }

    /**
     * @param string $message
     * @return ClaimCheck
     */
    private function storeMessageInS3($message)
    {
        $s3Client = $this->configuration->getS3Client();
        $s3BucketName = $this->configuration->getS3BucketName();
        $claimCheckFactory = $this->configuration->getClaimCheckFactory();
        
        $claimCheck = $claimCheckFactory->create();

        $s3Client->putObject([
            'Bucket' => $s3BucketName,
            'Key' => $claimCheck->getS3Key(),
            'Body' => $message,
        ]);
        
        return $claimCheck;
    }

    /**
     * @param ClaimCheck $claimCheck
     * @return string
     * @throws ExceptionInterface
     */
    private function fetchClaimCheckFromS3(ClaimCheck $claimCheck)
    {
        $s3Client = $this->configuration->getS3Client();

        $result = $s3Client->getObject([
            'Bucket' => $claimCheck->getS3BucketName(),
            'Key' => $claimCheck->getS3Key(),
        ]);

        $body = $result->get('Body');

        // Unpack the message.
        if ($body instanceof StreamInterface) {
            try {
                return $body->getContents();
            } catch (\RuntimeException $e) {
                throw new RuntimeException($e->getMessage(), 0, $e);
            }
        }

        return $body;
    }

    /**
     * @param string $receiptHandle
     * @param string $s3MsgBucketName
     * @param string $s3MsgKey
     * @return string
     */
    private function embedS3PointerInReceiptHandle($receiptHandle, $s3MsgBucketName, $s3MsgKey)
    {
        return json_encode(array(
            'original_receipt_handle' => $receiptHandle,
            's3_bucket_name' => $s3MsgBucketName,
            's3_key' => $s3MsgKey,
        ));
    }
}
