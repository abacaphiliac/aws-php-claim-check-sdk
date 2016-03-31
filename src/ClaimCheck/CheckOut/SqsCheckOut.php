<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\CheckOut;

use Abacaphiliac\AwsSdk\ClaimCheck\DataStore\S3DataStore;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Abacaphiliac\AwsSdk\ClaimCheck\Message;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckJsonSerializer;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\JsonSnsMessageSerializer;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerChain;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;

class SqsCheckOut implements CheckOutInterface
{
    /** @var  SqsClient */
    private $sqsClient;
    
    /** @var  string */
    private $queueUrl;
    
    /** @var  ClaimCheckSerializerInterface */
    private $claimCheckSerializer;
    
    /** @var  S3Client */
    private $s3Client;

    /**
     * SqsCheckOut constructor.
     * @param SqsClient $sqsClient
     * @param string $queueUrl
     * @param ClaimCheckSerializerInterface $claimCheckSerializer
     * @param S3Client $s3Client
     */
    public function __construct(
        SqsClient $sqsClient,
        $queueUrl,
        S3Client $s3Client,
        ClaimCheckSerializerInterface $claimCheckSerializer = null
    ) {
        if (!$claimCheckSerializer) {
            $claimCheckSerializer = new ClaimCheckSerializerChain([
                new ClaimCheckJsonSerializer(),
                new JsonSnsMessageSerializer(),
            ]);
        }
        
        $this->sqsClient = $sqsClient;
        $this->queueUrl = $queueUrl;
        $this->s3Client = $s3Client;
        $this->claimCheckSerializer = $claimCheckSerializer;
    }

    /**
     * @return Message|bool
     * @throws ExceptionInterface
     */
    public function fetch()
    {
        // Get the message(s).
        $sqsResult = $this->sqsClient->receiveMessage([
            'QueueUrl' => $this->queueUrl,
            'MessageAttributeNames' => ['All'],
            'MaxNumberOfMessages' => 1, // TODO Support for more than one message.
        ]);

        $sqsMessageBody = $sqsResult->search('Messages[].Body|[0]');
        if (!$sqsMessageBody) {
            return false;
        }
        
        $message = new Message();
        $message->setContent($sqsMessageBody);
        
        // TODO Delete immediately? Or only after successful parsing?
        $this->sqsClient->deleteMessage([
            'QueueUrl' => $this->queueUrl,
            'ReceiptHandle' => $sqsResult->search('Messages[].ReceiptHandle|[0]'),
        ]);

        try {
            // Attempt to decode message body.
            $claimCheck = $this->claimCheckSerializer->unserialize($sqsMessageBody);
            $message->setClaimCheck($claimCheck);
        } catch (InvalidArgumentException $e) {
            return $message;
        }

        // Fetch actual message from data store.
        $checkOut = new S3DataStore($this->s3Client, $claimCheck->getS3BucketName());
        $s3MessageBody = $checkOut->fetch($claimCheck->getS3Key());
        $message->setContent($s3MessageBody);
        
        return $message;
    }
}
