<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\Serializer;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\JsonSnsMessageSerializer;
use Zend\Json\Json;

class JsonSnsMessageSerializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var JsonSnsMessageSerializer */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new JsonSnsMessageSerializer();
    }
    
    public function testSerialize()
    {
        $claimCheck = new ClaimCheck('MyBucket', 'MyKey');

        $encodedValue = $this->sut->serialize($claimCheck);

        $actual = Json::decode($encodedValue, Json::TYPE_ARRAY);

        self::assertArrayHasKey('s3BucketName', $actual);
        self::assertEquals('MyBucket', $actual['s3BucketName']);
        self::assertArrayHasKey('s3Key', $actual);
        self::assertEquals('MyKey', $actual['s3Key']);
    }

    public function testUnserialize()
    {
        $encodedValue = Json::encode([
            'Message' => Json::encode([
                's3BucketName' => 'MyBucket',
                's3Key' => 'MyKey',
            ]),
        ]);

        $actual = $this->sut->unserialize($encodedValue);

        self::assertEquals('MyBucket', $actual->getS3BucketName());
        self::assertEquals('MyKey', $actual->getS3Key());
    }

    /**
     * @throws ExceptionInterface
     * @expectedException \Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface
     */
    public function testUnserializeInvalidJson()
    {
        $this->sut->unserialize('StuffAndThings');
    }

    /**
     * @throws ExceptionInterface
     * @expectedException \Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface
     */
    public function testUnserializeClaimCheckMissingBucketName()
    {
        $this->sut->unserialize(Json::encode([
            's3BucketName' => 'MyBucketName',
            's3Key' => 'MyKey',
        ]));
    }
}
