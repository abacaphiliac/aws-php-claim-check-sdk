<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Sns;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactoryInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckJsonSerializer;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerChain;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\JsonSnsMessageSerializer;
use Aws\S3\S3Client;

class SnsExtendedClientConfiguration
{
    /** @var  S3Client */
    private $s3Client;
    
    /** @var  string */
    private $s3BucketName;
    
    /** @var  ClaimCheckFactoryInterface */
    private $claimCheckFactory;
    
    /** @var  ClaimCheckSerializerInterface */
    private $claimCheckSerializer;

    /**
     * ExtendedClientConfiguration constructor.
     * @param S3Client $s3Client
     * @param string $s3BucketName
     * @throws ExceptionInterface
     */
    public function __construct(S3Client $s3Client, $s3BucketName)
    {
        if (!$s3BucketName) {
            throw new InvalidArgumentException('S3 Bucket Name is required and cannot be empty.');
        }
        
        $this->s3Client = $s3Client;
        $this->s3BucketName = $s3BucketName;
        $this->claimCheckFactory = new ClaimCheckFactory($s3BucketName);
        $this->claimCheckSerializer = new ClaimCheckSerializerChain(array(
            new ClaimCheckJsonSerializer(),
            new JsonSnsMessageSerializer(),
        ));
    }

    /**
     * @return S3Client
     */
    public function getS3Client()
    {
        return $this->s3Client;
    }

    /**
     * @param S3Client $s3Client
     */
    public function setS3Client($s3Client)
    {
        $this->s3Client = $s3Client;
    }

    /**
     * @return string
     */
    public function getS3BucketName()
    {
        return $this->s3BucketName;
    }

    /**
     * @param string $s3BucketName
     */
    public function setS3BucketName($s3BucketName)
    {
        $this->s3BucketName = $s3BucketName;
    }

    /**
     * @return ClaimCheckFactoryInterface
     */
    public function getClaimCheckFactory()
    {
        return $this->claimCheckFactory;
    }

    /**
     * @param ClaimCheckFactoryInterface $claimCheckFactory
     */
    public function setClaimCheckFactory($claimCheckFactory)
    {
        $this->claimCheckFactory = $claimCheckFactory;
    }

    /**
     * @return ClaimCheckSerializerInterface
     */
    public function getClaimCheckSerializer()
    {
        return $this->claimCheckSerializer;
    }

    /**
     * @param ClaimCheckSerializerInterface $claimCheckSerializer
     */
    public function setClaimCheckSerializer(ClaimCheckSerializerInterface $claimCheckSerializer)
    {
        $this->claimCheckSerializer = $claimCheckSerializer;
    }
}
