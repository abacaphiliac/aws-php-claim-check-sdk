<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck;

use Ramsey\Uuid\Uuid;

class ClaimCheck
{
    /** @var  string */
    private $s3BucketName;
    
    /** @var  string */
    private $s3Key;

    /**
     * ClaimCheckMessage constructor.
     * @param string $s3BucketName
     * @param string $s3Key
     */
    public function __construct($s3BucketName, $s3Key = null)
    {
        $this->s3BucketName = $s3BucketName;
        
        if (!$s3Key) {
            $s3Key = Uuid::uuid4()->toString();
        }
        
        $this->s3Key = $s3Key;
    }

    /**
     * @return string
     */
    public function getS3BucketName()
    {
        return $this->s3BucketName;
    }

    /**
     * @return string
     */
    public function getS3Key()
    {
        return $this->s3Key;
    }
}
