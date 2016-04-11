<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck;

interface ClaimCheckFactoryInterface
{
    /**
     * @param string $s3Key
     * @return ClaimCheck
     */
    public function create($s3Key = null);
}
