<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck;

class ClaimCheckFactory implements ClaimCheckFactoryInterface
{
    /** @var  string */
    private $s3BucketName;

    /**
     * ClaimCheckFactory constructor.
     * @param string $s3BucketName
     */
    public function __construct($s3BucketName)
    {
        $this->s3BucketName = $s3BucketName;
    }

    /**
     * @param string $s3Key
     * @return ClaimCheck
     */
    public function create($s3Key = null)
    {
        return new ClaimCheck($this->s3BucketName, $s3Key);
    }
}
