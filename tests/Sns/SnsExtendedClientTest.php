<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\Sns;

use Abacaphiliac\AwsSdk\ClaimCheck\Sns\SnsExtendedClient;
use Abacaphiliac\AwsSdk\ClaimCheck\Sns\SnsExtendedClientConfiguration;
use Aws\Result;
use Aws\S3\S3Client;
use Aws\Sns\SnsClient;

class SnsExtendedClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|S3Client */
    private $s3Client;
    
    /** @var  string */
    private $s3BucketName = 'MyBucketName';
    
    /** @var  \PHPUnit_Framework_MockObject_MockObject|SnsClient */
    private $snsClient;
    
    /** @var  \PHPUnit_Framework_MockObject_MockObject|SnsExtendedClientConfiguration */
    private $configuration;
    
    /** @var SnsExtendedClient */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->snsClient = $this->getMockBuilder(SnsClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->s3Client = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->configuration = new SnsExtendedClientConfiguration($this->s3Client, $this->s3BucketName);
        
        $this->sut = new SnsExtendedClient($this->snsClient, $this->configuration);
    }
    
    public function testPublishAlteredMessage()
    {
        $args = [
            'Message' => $originalMessage = 'MyOriginalMessage',
        ];
        
        $this->snsClient->method('__call')->with('publish')
            ->willReturnCallback(function ($name, array $args) use ($originalMessage) {
                $params = array_key_exists(0, $args) ? $args[0] : [];
                
                \PHPUnit_Framework_Assert::assertNotEquals($originalMessage, $params['Message']);
                
                return new Result();
            });

        $actual = $this->sut->publish($args);
        
        self::assertInstanceOf(Result::class, $actual);
    }
}
