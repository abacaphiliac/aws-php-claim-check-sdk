<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\CheckIn;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactoryInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\DataStore\DataStoreInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Message;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckJsonSerializer;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Transformer\MessageAttributesTransformer;
use Aws\Sns\SnsClient;

class SnsCheckIn implements CheckInInterface
{
    /** @var  DataStoreInterface */
    private $dataStore;
    
    /** @var  SnsClient */
    private $snsClient;
    
    /** @var  string */
    private $topicArn;
    
    /** @var  ClaimCheckFactoryInterface */
    private $claimFactory;
    
    /** @var  ClaimCheckSerializerInterface */
    private $claimSerializer;

    /**
     * CheckLuggage constructor.
     * @param SnsClient $snsClient
     * @param string $topicArn
     * @param DataStoreInterface $dataStore
     * @param ClaimCheckFactoryInterface $claimFactory
     * @param ClaimCheckSerializerInterface $claimSerializer
     */
    public function __construct(
        SnsClient $snsClient,
        $topicArn,
        DataStoreInterface $dataStore,
        ClaimCheckFactoryInterface $claimFactory,
        ClaimCheckSerializerInterface $claimSerializer = null
    ) {
        if (!$claimSerializer) {
            $claimSerializer = new ClaimCheckJsonSerializer();
        }
        
        $this->snsClient = $snsClient;
        $this->topicArn = $topicArn;
        $this->dataStore = $dataStore;
        $this->claimFactory = $claimFactory;
        $this->claimSerializer = $claimSerializer;
    }

    /**
     * @param Message $message
     * @return Message
     */
    public function store(Message $message)
    {
        $claimCheck = $this->claimFactory->create();
        $message->setClaimCheck($claimCheck);
        
        $this->dataStore->store($claimCheck->getS3Key(), $message->getContent());

        $args = [
            'TopicArn' => $this->topicArn,
            'Message' => $this->claimSerializer->serialize($claimCheck),
        ];
        
        $attributes = $message->getAttributes();
        if ($attributes) {
            $attributes = MessageAttributesTransformer::transformToMessageArgs($attributes);
            if ($attributes) {
                $args['MessageAttributes'] = $attributes;
            }
        }

        $this->snsClient->publish($args);
        
        return $message;
    }
}
