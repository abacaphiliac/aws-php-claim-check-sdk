<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Serializer;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;

class ClaimCheckSerializerChain implements ClaimCheckSerializerInterface
{
    /** @var  ClaimCheckSerializerInterface[] */
    private $serializers;

    /**
     * SerializerChain constructor.
     * @param ClaimCheckSerializerInterface[] $serializers
     */
    public function __construct(array $serializers)
    {
        $this->setSerializers($serializers);
    }

    /**
     * @param ClaimCheck $claimCheck
     * @return string|bool
     * @throws ExceptionInterface
     */
    public function serialize(ClaimCheck $claimCheck)
    {
        foreach ($this->serializers as $serializer) {
            try {
                return $serializer->serialize($claimCheck);
            } catch (InvalidArgumentException $e) {
                
            }
        }
        
        throw new InvalidArgumentException('Failed to serialize Claim Check.');
    }

    /**
     * @param string $encodedValue
     * @return ClaimCheck
     * @throws ExceptionInterface
     */
    public function unserialize($encodedValue)
    {
        foreach ($this->serializers as $serializer) {
            try {
                return $serializer->unserialize($encodedValue);
            } catch (InvalidArgumentException $e) {

            }
        }

        throw new InvalidArgumentException('Failed to hydrate Claim Check from message: ' . $encodedValue);
    }

    /**
     * @return ClaimCheckSerializerInterface[]
     */
    public function getSerializers()
    {
        return $this->serializers;
    }

    /**
     * @param ClaimCheckSerializerInterface[] $serializers
     */
    public function setSerializers(array $serializers)
    {
        $this->serializers = array();
        
        array_map(array($this, 'addSerializer'), $serializers);
    }

    /**
     * @param ClaimCheckSerializerInterface $serializer
     */
    public function addSerializer(ClaimCheckSerializerInterface $serializer)
    {
        $this->serializers[] = $serializer;
    }
}
