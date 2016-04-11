<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Serializer;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json;

class JsonSnsMessageSerializer implements ClaimCheckSerializerInterface
{
    /** @var  ClaimCheckJsonSerializer */
    private $serializer;

    /**
     * JsonSnsMessageSerializer constructor.
     */
    public function __construct()
    {
        $this->serializer = new ClaimCheckJsonSerializer();
    }

    /**
     * @param ClaimCheck $claimCheck
     * @return string
     * @throws ExceptionInterface
     */
    public function serialize(ClaimCheck $claimCheck)
    {
        return $this->serializer->serialize($claimCheck);
    }

    /**
     * @param string $encodedValue
     * @return ClaimCheck
     * @throws ExceptionInterface
     */
    public function unserialize($encodedValue)
    {
        try {
            $data = Json::decode($encodedValue, Json::TYPE_ARRAY);
        } catch (RuntimeException $e) {
            throw new InvalidArgumentException($e->getMessage(), 0, $e);
        }
        
        if (!array_key_exists('Message', $data)) {
            throw new InvalidArgumentException('Message is required and cannot be empty.');
        }
        
        return $this->serializer->unserialize($data['Message']);
    }
}
