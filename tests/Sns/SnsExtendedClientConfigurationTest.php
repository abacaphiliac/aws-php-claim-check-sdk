<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\Sns;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactoryInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerChain;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Sns\SnsExtendedClientConfiguration;
use Aws\S3\S3Client;

class SnsExtendedClientConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|S3Client */
    private $s3Client;
    
    /** @var  string */
    private $s3BucketName = 'MyBucket';
    
    /** @var SnsExtendedClientConfiguration */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->s3Client = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->sut = new SnsExtendedClientConfiguration($this->s3Client, $this->s3BucketName);
    }
    
    public function testGetS3Client()
    {
        self::assertSame($this->s3Client, $this->sut->getS3Client());

        /** @var S3Client $expected */
        $expected = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->sut->setS3Client($expected);

        self::assertSame($expected, $this->sut->getS3Client());
    }
    
    public function testGetS3BucketName()
    {
        self::assertEquals($this->s3BucketName, $this->sut->getS3BucketName());

        $this->sut->setS3BucketName($expected = 'AnotherBucket');

        self::assertSame($expected, $this->sut->getS3BucketName());
    }
    
    public function testGetClaimCheckFactory()
    {
        self::assertInstanceOf(ClaimCheckFactoryInterface::class, $this->sut->getClaimCheckFactory());
        
        $this->sut->setClaimCheckFactory($expected = new ClaimCheckFactory('AnotherBucket'));
        
        self::assertSame($expected, $this->sut->getClaimCheckFactory());
    }
    
    public function testGetClaimCheckSerializer()
    {
        self::assertInstanceOf(ClaimCheckSerializerInterface::class, $this->sut->getClaimCheckSerializer());

        $this->sut->setClaimCheckSerializer($expected = new ClaimCheckSerializerChain());

        self::assertSame($expected, $this->sut->getClaimCheckSerializer());
    }

    /**
     * @throws ExceptionInterface
     * @expectedException \Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface
     */
    public function testNullIsInvalidBucketName()
    {
        new SnsExtendedClientConfiguration($this->s3Client, null);
    }

    /**
     * @throws ExceptionInterface
     * @expectedException \Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface
     */
    public function testEmptyStringIsInvalidBucketName()
    {
        new SnsExtendedClientConfiguration($this->s3Client, '');
    }
}
