<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\Sqs;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckJsonSerializer;
use Abacaphiliac\AwsSdk\ClaimCheck\Sqs\SqsExtendedClient;
use Abacaphiliac\AwsSdk\ClaimCheck\Sqs\SqsExtendedClientConfiguration;
use Aws\Result;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;

class SqsExtendedClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|S3Client */
    private $s3Client;

    /** @var  string */
    private $s3BucketName = 'MyBucketName';

    /** @var  \PHPUnit_Framework_MockObject_MockObject|SqsClient */
    private $sqsClient;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|SqsExtendedClientConfiguration */
    private $configuration;
    
    /** @var SqsExtendedClient */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sqsClient = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->s3Client = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configuration = new SqsExtendedClientConfiguration($this->s3Client, $this->s3BucketName);

        $this->sut = new SqsExtendedClient($this->sqsClient, $this->configuration);
    }

    public function testSendAlteredMessage()
    {
        $args = [
            'MessageBody' => $originalMessage = 'MyOriginalMessage',
        ];

        $this->sqsClient->method('__call')->with('sendMessage')
            ->willReturnCallback(function ($name, array $args) use ($originalMessage) {
                $params = array_key_exists(0, $args) ? $args[0] : [];

                \PHPUnit_Framework_Assert::assertNotEquals($originalMessage, $params['MessageBody']);

                return new Result();
            });

        $actual = $this->sut->sendMessage($args);

        self::assertInstanceOf(Result::class, $actual);
    }

    public function testReceiveUnalteredMessage()
    {
        $expected = 'MyOriginalMessage';
        
        $this->sqsClient->method('__call')->with('receiveMessage')
            ->willReturnCallback(function ($name, array $args) use ($expected) {
                return new Result([
                    'Messages' => [
                        [
                            'Body' => $expected,
                        ],
                    ],
                ]);
            });

        $actual = $this->sut->receiveMessage();

        self::assertInstanceOf(Result::class, $actual);
        
        self::assertEquals($expected, $actual->search('Messages[].Body|[0]'));
    }

    public function testReceiveAlteredMessage()
    {
        $originalMessage = 'MyOriginalMessage';
        
        $this->sqsClient->method('__call')->with('receiveMessage')
            ->willReturnCallback(function ($name, array $args) {
                $factory = new ClaimCheckFactory('MyBucket');
                $serializer = new ClaimCheckJsonSerializer();
                return new Result([
                    'Messages' => [
                        [
                            'Body' => $serializer->serialize($factory->create('MyKey')),
                        ],
                    ],
                ]);
            });

        $this->s3Client->method('__call')->with('getObject')
            ->willReturnCallback(function ($name, array $args) use ($originalMessage) {
                return new Result([
                    'Body' => $originalMessage,
                ]);
            });

        $actual = $this->sut->receiveMessage();

        self::assertInstanceOf(Result::class, $actual);
        
        self::assertEquals($originalMessage, $actual->search('Messages[].Body|[0]'));
    }

    public function testReceiveAlteredReceiptHandle()
    {
        $this->sqsClient->method('__call')->with('receiveMessage')
            ->willReturnCallback(function ($name, array $args) {
                $factory = new ClaimCheckFactory('MyBucket');
                $serializer = new ClaimCheckJsonSerializer();
                return new Result([
                    'Messages' => [
                        [
                            'Body' => $serializer->serialize($factory->create('MyKey')),
                            'ReceiptHandle' => 'MyReceiptHandle',
                        ],
                    ],
                ]);
            });

        $this->s3Client->method('__call')->with('getObject')
            ->willReturnCallback(function ($name, array $args) {
                return new Result([
                    'Body' => 'MyOriginalMessage',
                ]);
            });

        $actual = $this->sut->receiveMessage();

        self::assertInstanceOf(Result::class, $actual);
        
        self::assertContains('MyReceiptHandle', $actual->search('Messages[].ReceiptHandle|[0]'));
        self::assertContains('MyBucket', $actual->search('Messages[].ReceiptHandle|[0]'));
        self::assertContains('MyKey', $actual->search('Messages[].ReceiptHandle|[0]'));
    }
    
    public function testDeleteFromS3()
    {
        $args = [
            'ReceiptHandle' => $modifiedReceiptHandle = json_encode([
                's3_bucket_name' => 'MyBucket',
                's3_key' => 'MyKey',
                'original_receipt_handle' => 'MyReceiptHandle',
            ]),
        ];

        $this->sqsClient->method('__call')->with('deleteMessage')
            ->willReturnCallback(function ($name, array $args) use ($modifiedReceiptHandle) {
                $params = array_key_exists(0, $args) ? $args[0] : [];

                \PHPUnit_Framework_Assert::assertNotEquals($modifiedReceiptHandle, $params['ReceiptHandle']);

                return new Result();
            });
        
        $actual = $this->sut->deleteMessage($args);
        
        self::assertInstanceOf(Result::class, $actual);
    }
}
