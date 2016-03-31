<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

use Abacaphiliac\AwsSdk\ClaimCheck\DataStore\S3DataStore;
use Abacaphiliac\AwsSdk\ClaimCheck\CheckIn\SqsCheckIn;
use Abacaphiliac\AwsSdk\ClaimCheck\CheckOut\SqsCheckOut;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\RuntimeException;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\UnexpectedValueException;
use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\Message;
use Aws\S3\S3Client;
use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;

trait SqsContextTrait
{
    /** @var  SqsClient */
    private $sqsClient;
    
    /** @var  string */
    private $queueUrl;
    
    /** @var  string[] */
    private $queueUrls = [];

    /**
     * @return S3Client
     */
    abstract public function getS3Client();

    /**
     * @return S3DataStore
     */
    abstract public function createS3DataStore();

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
     */
    public function getSqsClient()
    {
        if (!$this->sqsClient) {
            $config = $this->getAwsServiceConfig();

            $this->sqsClient = new SqsClient($config);
        }

        return $this->sqsClient;
    }

    /**
     * @param string $queueUrl
     * @return SqsCheckIn
     */
    public function createSqsCheckIn($queueUrl)
    {
        $s3DataStore = $this->createS3DataStore();
        $s3ClaimCheckFactory = $this->createClaimCheckFactory();
        
        return new SqsCheckIn($this->sqsClient, $queueUrl, $s3DataStore, $s3ClaimCheckFactory);
    }

    /**
     * @param string $queueUrl
     * @return SqsCheckOut
     */
    public function createSqsCheckOut($queueUrl)
    {
        return new SqsCheckOut($this->sqsClient, $queueUrl, $this->getS3Client());
    }

    /**
     * @Given /^a queue named "([^"]*)"$/
     * @param string $name
     * @return string
     */
    public function getQueueFixture($name)
    {
        $this->queueUrl = $this->getQueueUrl($name);
        
        if (!$this->queueUrl) {
            $this->queueUrl = $this->createQueueFixture($name);
        }
        
        return $this->queueUrl;
    }

    /**
     * @param string $name
     * @return string|bool
     */
    public function getQueueUrl($name)
    {
        if (array_key_exists($name, $this->queueUrls)) {
            return $this->queueUrls[$name];
        }
        
        try {
            $result = $this->getSqsClient()->getQueueUrl([
                'QueueName' => $name,
            ]);

            $this->queueUrls[$name] = $result->get('QueueUrl');
            
            return $this->queueUrls[$name];
        } catch (SqsException $e) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return string
     */
    public function getQueueArn($name)
    {
        $queueUrl = $this->getQueueUrl($name);
        
        return $this->getSqsClient()->getQueueArn($queueUrl);
    }

    /**
     * @param string $name
     * @return string
     */
    public function createQueueFixture($name)
    {
        $result = $this->getSqsClient()->createQueue([
            'QueueName' => $name,
        ]);

        return $result->get('QueueUrl');
    }

    /**
     * @When /^I send the message to a queue named "([^"]*)"$/
     * @param string $name
     */
    public function iSendTheMessageToQueue($name)
    {
        $queueUrl = $this->getQueueUrl($name);

        $sqsCheckIn = $this->createSqsCheckIn($queueUrl);
        
        $message = new Message();
        $message->setContent($this->getMessage());
        
        $sqsCheckIn->store($message);
    }

    /**
     * @Then /^I can fetch the message from a queue named "([^"]*)"$/
     * @param string $name
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws UnexpectedValueException
     * @throws \RuntimeException
     */
    public function iCanFetchTheMessageFromQueue($name)
    {
        $queueUrl = $this->getQueueUrl($name);

        $sqsCheckOut = $this->createSqsCheckOut($queueUrl);
        
        $expectedMessage = $this->getMessage();

        $actualMessage = $sqsCheckOut->fetch();
        
        \PHPUnit_Framework_Assert::assertEquals($expectedMessage, $actualMessage->getContent());
    }
}
