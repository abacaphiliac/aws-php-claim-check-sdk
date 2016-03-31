<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Serializer;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Zend\Json\Exception\RuntimeException;
use Zend\Json\Json;

class ClaimCheckJsonSerializer implements ClaimCheckSerializerInterface
{
    /**
     * @param ClaimCheck $claimCheck
     * @return string
     * @throws ExceptionInterface
     */
    public function serialize(ClaimCheck $claimCheck)
    {
        return Json::encode(array(
            's3BucketName' => $claimCheck->getS3BucketName(),
            's3Key' => $claimCheck->getS3Key(),
        ));
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
        
        foreach (array('s3BucketName', 's3Key') as $param) {
            if (!array_key_exists($param, $data) || !$data[$param]) {
                throw new InvalidArgumentException(sprintf('Param %s is required and cannot be empty.', $param));
            }
        }
        
        return new ClaimCheck($data['s3BucketName'], $data['s3Key']);
    }
}
