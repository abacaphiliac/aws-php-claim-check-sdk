<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;
use Aws\S3\S3Client;

trait S3ContextTrait
{
    /** @var  string */
    private $bucketName;
    
    /** @var  S3Client */
    private $s3Client;

    /**
     * @return mixed[]
     */
    abstract public function getAwsServiceConfig();

    /**
     * @return S3Client
     * @throws \InvalidArgumentException
     */
    public function getS3Client()
    {
        if (!$this->s3Client) {
            $config = $this->getAwsServiceConfig();

            $this->s3Client = new S3Client($config);
        }

        return $this->s3Client;
    }

    /**
     * @Given /^a data store named "([^"]*)"$/
     * @param string $name
     * @return string
     * @throws \InvalidArgumentException
     * @throws \Aws\S3\Exception\S3Exception
     * @throws \Exception
     */
    public function getS3BucketFixture($name)
    {
        $this->getS3Client()->headBucket(array(
            'Bucket' => $name,
        ));
        
        $this->bucketName = $name;
    }

    /**
     * @return string
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }

    /**
     * @return ClaimCheckFactory
     */
    public function createClaimCheckFactory()
    {
        return new ClaimCheckFactory($this->bucketName);
    }
}
