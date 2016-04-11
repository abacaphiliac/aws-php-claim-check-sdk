<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\Sqs\SqsExtendedClientConfiguration;
use Abacaphiliac\AwsSdk\ClaimCheck\Sqs\SqsExtendedClient;
use Aws\Result;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;

trait SqsContextTrait
{
    /** @var  SqsClient */
    private $sqsClient;
    
    /** @var  SqsExtendedClientConfiguration */
    private $sqsExtendedClientConfiguration;
    
    /** @var  string */
    private $queueUrl;
    
    /** @var  string[] */
    private $queueUrls = [];

    /**
     * @return S3Client
     */
    abstract public function getS3Client();

    /**
     * @return string
     */
    abstract public function getBucketName();

    /**
     * @return string
     */
    abstract public function getMessage();

    /**
     * @return mixed[]
     */
    abstract public function getAwsServiceConfig();

    /**
     * @return ClaimCheckFactory
     */
    abstract public function createClaimCheckFactory();

    /**
     * @return SqsClient
     * @throws ExceptionInterface
     */
    public function getSqsClient()
    {
        if (!$this->sqsClient) {
            $config = $this->getAwsServiceConfig();
            $client = new SqsClient($config);
            $this->sqsClient = new SqsExtendedClient($client, $this->getSqsExtendedClientConfiguration());
        }

        return $this->sqsClient;
    }

    /**
     * @return SqsExtendedClientConfiguration
     * @throws ExceptionInterface
     */
    public function getSqsExtendedClientConfiguration()
    {
        if (!$this->sqsExtendedClientConfiguration) {
            $s3Client = $this->getS3Client();

            $s3BucketName = $this->getBucketName();

            $this->sqsExtendedClientConfiguration = new SqsExtendedClientConfiguration($s3Client, $s3BucketName);
        }
        
        return $this->sqsExtendedClientConfiguration;
    }

    /**
     * @Given /^a queue named "([^"]*)"$/
     * @param string $name
     * @return string
     * @throws ExceptionInterface
     */
    public function getQueueFixture($name)
    {
        $this->queueUrl = $this->getQueueUrl($name);
        
        return $this->queueUrl;
    }

    /**
     * @param string $name
     * @return string|bool
     * @throws ExceptionInterface
     */
    public function getQueueUrl($name)
    {
        if (array_key_exists($name, $this->queueUrls)) {
            return $this->queueUrls[$name];
        }
        
        $result = $this->getSqsClient()->getQueueUrl([
            'QueueName' => $name,
        ]);

        $this->queueUrls[$name] = $result->get('QueueUrl');
        
        return $this->queueUrls[$name];
    }

    /**
     * @param string $name
     * @return string
     * @throws ExceptionInterface
     */
    public function getQueueArn($name)
    {
        $queueUrl = $this->getQueueUrl($name);
        
        return $this->getSqsClient()->getQueueArn($queueUrl);
    }

    /**
     * @When /^I send the message to a queue named "([^"]*)"$/
     * @param string $name
     * @return Result
     * @throws ExceptionInterface
     */
    public function iSendTheMessageToQueue($name)
    {
        $sqsClient = $this->getSqsClient();
        $queueUrl = $this->getQueueUrl($name);
        $message = $this->getMessage();

        return $sqsClient->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $message,
        ]);
    }

    /**
     * @Then /^I can fetch the message from a queue named "([^"]*)"$/
     * @param string $name
     * @return Result
     * @throws ExceptionInterface
     * @throws \RuntimeException
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public function iCanFetchTheMessageFromQueue($name)
    {
        $expectedMessage = $this->getMessage();
        \PHPUnit_Framework_Assert::assertNotEmpty($expectedMessage);

        $sqsClient = $this->getSqsClient();
        
        $queueUrl = $this->getQueueUrl($name);
        
        $result = $sqsClient->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => 1,
        ]);
        
        $actualMessage = $result->search('Messages[].Body|[0]');
        \PHPUnit_Framework_Assert::assertNotEmpty($actualMessage);
        
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Purge the queues before running the feature suite.'
        );

        $sqsClient->deleteMessage([
            'QueueUrl' => $queueUrl,
            'ReceiptHandle' => $result->search('Messages[].ReceiptHandle|[0]'),
        ]);
        
        return $result;
    }
}
