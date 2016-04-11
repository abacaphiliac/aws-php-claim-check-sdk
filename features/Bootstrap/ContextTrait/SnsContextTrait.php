<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Sns\SnsExtendedClient;
use Abacaphiliac\AwsSdk\ClaimCheck\Sns\SnsExtendedClientConfiguration;
use Abacaphiliac\AwsSdk\ClaimCheck\Sqs\SqsExtendedClientConfiguration;
use Aws\Result;
use Aws\S3\S3Client;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;

trait SnsContextTrait
{
    /** @var  SnsClient */
    private $snsClient;
    
    /** @var  string */
    private $topicArn;

    /**
     * @return S3Client
     */
    abstract public function getS3Client();

    /**
     * @return string
     */
    abstract public function getBucketName();

    /**
     * @return mixed[]
     */
    abstract public function getAwsServiceConfig();

    /**
     * @return ClaimCheckFactory
     */
    abstract public function createClaimCheckFactory();

    /**
     * @param string $name
     * @return string
     */
    abstract public function getQueueUrl($name);

    /**
     * @return string
     */
    abstract public function getMessage();

    /**
     * @return SqsClient
     */
    abstract public function getSqsClient();

    /**
     * @return SqsExtendedClientConfiguration
     * @throws ExceptionInterface
     */
    abstract public function getSqsExtendedClientConfiguration();

    /**
     * @return SnsClient
     * @throws \InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function getSnsClient()
    {
        if (!$this->snsClient) {
            $config = $this->getAwsServiceConfig();
            $client = new SnsClient($config);
            $this->snsClient = new SnsExtendedClient($client, $this->createSnsExtendedClientConfiguration());
        }

        return $this->snsClient;
    }

    /**
     * @return SnsExtendedClientConfiguration
     * @throws ExceptionInterface
     */
    private function createSnsExtendedClientConfiguration()
    {
        $s3Client = $this->getS3Client();

        $s3BucketName = $this->getBucketName();

        return new SnsExtendedClientConfiguration($s3Client, $s3BucketName);
    }

    /**
     * @Given /^a topic named "([^"]*)"$/
     * @param string $name
     * @return string
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     */
    public function getTopicFixture($name)
    {
        $this->topicArn = $this->createTopicFixture($name);
        
        return $this->topicArn;
    }

    /**
     * @param string $name
     * @return string
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     */
    public function createTopicFixture($name)
    {
        $result = $this->getSnsClient()->createTopic([
            'Name' => $name,
        ]);

        return $result->get('TopicArn');
    }

    /**
     * @Given /^a queue named "([^"]*)" is subscribed to a topic named "([^"]*)"$/
     * @param string $queueName
     * @param string $topicName
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     * @throws \Zend\Json\Exception\RuntimeException
     */
    public function aQueueNamedIsSubscribedToATopicNamed($queueName, $topicName)
    {
        $topicArn = $this->createTopicFixture($topicName);

        $queueUrl = $this->getQueueUrl($queueName);
        
        $queueArn = $this->getSqsClient()->getQueueArn($queueUrl);
        
        // Subscribe SQS Queue to SNS Topic.
        $this->getSnsClient()->subscribe([
            'TopicArn' => $topicArn,
            'Protocol' => 'sqs',
            'Endpoint' => $queueArn,
        ]);
    }

    /**
     * @When /^I send the message to a topic named "([^"]*)"$/
     * @param string $name
     * @return Result
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     */
    public function iSendTheMessageToATopicNamed($name)
    {
        $sqsExtendedClientConfiguration = $this->getSqsExtendedClientConfiguration();
        
        // Leaky abstraction???
        // We probably shouldn't be able to change the behavior of the extended-client after it is created.
        $sqsExtendedClientConfiguration->setDeleteFromS3(false);
        
        $topicArn = $this->createTopicFixture($name);

        $result = $this->getSnsClient()->publish([
            'TopicArn' => $topicArn,
            'Message' => $this->getMessage(),
        ]);
        
        return $result;
    }

}
