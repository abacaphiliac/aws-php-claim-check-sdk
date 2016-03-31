<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\DataStore;

use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\RuntimeException;
use Aws\S3\S3Client;
use Psr\Http\Message\StreamInterface;

class S3DataStore implements DataStoreInterface
{
    /** @var  S3Client */
    private $s3Client;
    
    /** @var  string */
    private $bucket;

    /**
     * CheckLuggage constructor.
     * @param S3Client $s3Client
     * @param string $bucket
     */
    public function __construct(S3Client $s3Client, $bucket)
    {
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
    }

    /**
     * @param string $key
     * @return string
     * @throws ExceptionInterface
     */
    public function fetch($key)
    {
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $body = $result->get('Body');

        // Unpack the message.
        if ($body instanceof StreamInterface) {
            try {
                return $body->getContents();
            } catch (\RuntimeException $e) {
                throw new RuntimeException($e->getMessage(), 0, $e);
            }
        }

        return $body;
    }

    /**
     * @param string $key
     * @param string $body
     * @return void
     */
    public function store($key, $body)
    {
        $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $body,
        ]);
    }
}
