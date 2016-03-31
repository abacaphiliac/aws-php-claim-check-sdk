<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Serializer;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;

interface ClaimCheckSerializerInterface
{
    /**
     * @param ClaimCheck $claimCheck
     * @return string
     * @throws ExceptionInterface
     */
    public function serialize(ClaimCheck $claimCheck);
    
    /**
     * @param string $encodedValue
     * @return ClaimCheck
     * @throws ExceptionInterface
     */
    public function unserialize($encodedValue);
}
