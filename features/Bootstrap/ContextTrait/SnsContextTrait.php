<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

use Abacaphiliac\AwsSdk\ClaimCheck\CheckIn\SnsCheckIn;
use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\DataStore\S3DataStore;
use Abacaphiliac\AwsSdk\ClaimCheck\Message;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Zend\Json\Json;

trait SnsContextTrait
{
    /** @var  SnsClient */
    private $snsClient;
    
    /** @var  string */
    private $topicArn;

    /**
     * @return S3DataStore
     */
    abstract public function createS3DataStore();

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
     * @return SnsClient
     * @throws \InvalidArgumentException
     */
    public function getSnsClient()
    {
        if (!$this->snsClient) {
            $config = $this->getAwsServiceConfig();

            $this->snsClient = new SnsClient($config);
        }

        return $this->snsClient;
    }

    /**
     * @param string $topicArn
     * @return SnsCheckIn
     */
    public function createSnsCheckIn($topicArn)
    {
        $s3DataStore = $this->createS3DataStore();
        $s3ClaimCheckFactory = $this->createClaimCheckFactory();

        return new SnsCheckIn($this->snsClient, $topicArn, $s3DataStore, $s3ClaimCheckFactory);
    }

    /**
     * @Given /^a topic named "([^"]*)"$/
     * @param string $name
     * @return string
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
        
        // Get SQS Policies.
        $result = $this->getSqsClient()->getQueueAttributes([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['Policy'],
        ]);

        $encodedValue = $result->search('Attributes.Policy');
        
        // Decode policy, and create a fallback in case this is a new queue without a policy.
        $policy = Json::decode($encodedValue, Json::TYPE_ARRAY) ?: [
            'Version' => '2012-10-17',
            'Id' => $queueArn . '/SQSDefaultPolicy',
        ];
        
        // Get policy statements.
        $statements = [];
        if (array_key_exists('Statement', $policy) && is_array($policy['Statement'])) {
            $statements = $policy['Statement'];
        }
        
        // Create the desired policy, which will allow the SNS Topic to send messages to the SQS Queue.
        $expectedStatement = [
            'Sid' => $expectedStatementId = 'Sid' . md5(__METHOD__),
            'Effect' => 'Allow',
            'Principal' => [
                'AWS' => '*'
            ],
            'Action' => 'SQS:SendMessage',
            'Resource' => $queueArn,
            'Condition' => [
                'ArnEquals' => [
                    'aws:SourceArn' => $topicArn,
                ],
            ],
        ];
        
        // Find the existing test policy so that it can be updated.
        $actualStatementIndex = false;
        foreach ($statements as $i => $statement) {
            if (array_key_exists('Sid', $statement) && $statement['Sid'] === $expectedStatementId) {
                $actualStatementIndex = $i;
                break;
            }
        }
        
        // Update the existing test policy, or append our new policy.
        if ($actualStatementIndex === false) {
            $policy['Statement'][] = $expectedStatement;
        } else {
            $policy['Statement'][$actualStatementIndex] = $expectedStatement;
        }

        // Update SQS policy.
        $this->getSqsClient()->setQueueAttributes([
            'QueueUrl' => $queueUrl,
            'Attributes' => [
                'Policy' => json_encode($policy, JSON_UNESCAPED_SLASHES),
            ],
        ]);
    }

    /**
     * @When /^I send the message to a topic named "([^"]*)"$/
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function iSendTheMessageToATopicNamed($name)
    {
        $topicArn = $this->createTopicFixture($name);

        $message = new Message();
        $message->setContent($this->getMessage());

        $snsCheckIn = $this->createSnsCheckIn($topicArn);
        
        $snsCheckIn->store($message);
    }

}
