<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\CheckIn;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactoryInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\DataStore\DataStoreInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Message;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckJsonSerializer;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Transformer\MessageAttributesTransformer;
use Aws\Sqs\SqsClient;

class SqsCheckIn implements CheckInInterface
{
    /** @var  DataStoreInterface */
    private $dataStore;

    /** @var  SqsClient */
    private $sqsClient;

    /** @var  string */
    private $queueUrl;

    /** @var  ClaimCheckFactoryInterface */
    private $claimFactory;

    /** @var  ClaimCheckSerializerInterface */
    private $claimSerializer;

    /**
     * CheckIn constructor.
     * @param SqsClient $sqsClient
     * @param string $queueUrl
     * @param DataStoreInterface $dataStore
     * @param ClaimCheckFactoryInterface $claimFactory
     * @param ClaimCheckSerializerInterface $claimSerializer
     */
    public function __construct(
        SqsClient $sqsClient,
        $queueUrl,
        DataStoreInterface $dataStore,
        ClaimCheckFactoryInterface $claimFactory,
        ClaimCheckSerializerInterface $claimSerializer = null
    ) {
        if (!$claimSerializer) {
            $claimSerializer = new ClaimCheckJsonSerializer();
        }
        
        $this->sqsClient = $sqsClient;
        $this->queueUrl = $queueUrl;
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
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $this->claimSerializer->serialize($claimCheck),
        ];

        $attributes = $message->getAttributes();
        if ($attributes) {
            $attributes = MessageAttributesTransformer::transformToMessageArgs($attributes);
            if ($attributes) {
                $args['MessageAttributes'] = $attributes;
            }
        }
        
        $this->sqsClient->sendMessage($args);
        
        return $message;
    }
}
