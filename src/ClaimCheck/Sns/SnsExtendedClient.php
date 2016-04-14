<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Sns;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Aws\Result;
use Aws\Sns\SnsClient;
use GuzzleHttp\Promise\Promise;

class SnsExtendedClient extends SnsClient
{
    /** @var  SnsClient */
    private $snsClient;
    
    /** @var  SnsExtendedClientConfiguration */
    private $configuration;

    /**
     * SnsExtendedClient constructor.
     * @param SnsClient $snsClient
     * @param SnsExtendedClientConfiguration $configuration
     */
    public function __construct(SnsClient $snsClient, SnsExtendedClientConfiguration $configuration)
    {
        $this->snsClient = $snsClient;
        $this->configuration = $configuration;
    }

    /**
     * @param string $name
     * @param mixed[] $args
     * @return Result|Promise
     * @throws ExceptionInterface
     */
    public function __call($name, array $args)
    {
        $params = array_key_exists(0, $args) ? $args[0] : [];
        
        if (strcasecmp($name, 'publish') === 0) {
            return $this->publishClaimCheck($params);
        }
        
        return $this->snsClient->{$name}($params);
    }

    /**
     * @param mixed[] $args
     * @return Result
     * @throws ExceptionInterface
     */
    private function publishClaimCheck(array $args = [])
    {
        $claimCheckSerializer = $this->configuration->getClaimCheckSerializer();

        $message = array_key_exists('Message', $args) ? $args['Message'] : '';

        $claimCheck = $this->storeMessageInS3($message);

        $args['Message'] = $claimCheckSerializer->serialize($claimCheck);

        return $this->snsClient->publish($args);
    }

    /**
     * @param string $message
     * @return ClaimCheck
     */
    private function storeMessageInS3($message)
    {
        $s3Client = $this->configuration->getS3Client();
        $s3BucketName = $this->configuration->getS3BucketName();
        $claimCheckFactory = $this->configuration->getClaimCheckFactory();

        $claimCheck = $claimCheckFactory->create();

        $s3Client->putObject([
            'Bucket' => $s3BucketName,
            'Key' => $claimCheck->getS3Key(),
            'Body' => $message,
        ]);

        return $claimCheck;
    }
}
