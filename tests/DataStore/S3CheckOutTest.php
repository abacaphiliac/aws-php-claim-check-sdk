<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\CheckOut;

use Abacaphiliac\AwsSdk\ClaimCheck\DataStore\S3DataStore;
use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class S3CheckOutTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|StreamInterface */
    private $stream;
    
    /** @var  \PHPUnit_Framework_MockObject_MockObject|S3Client */
    private $s3Client;
    
    private $bucket = 'MyBucket';
    
    /** @var S3DataStore */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->s3Client = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->stream = $this->getMock(StreamInterface::class);
        
        $this->sut = new S3DataStore($this->s3Client, $this->bucket);
    }
    
    public function testFetchString()
    {
        $this->s3Client->method('__call')->with('getObject')->willReturn(new Result([
            'Body' => $expected = 'StuffAndThings',
        ]));
        
        $actual = $this->sut->fetch('MyKey');
        
        self::assertEquals($expected, $actual);
    }
    
    public function testFetchStream()
    {
        $this->stream->method('getContents')->willReturn($expected = 'StuffAndThings');

        $this->s3Client->method('__call')->with('getObject')->willReturn(new Result([
            'Body' => $this->stream,
        ]));

        $actual = $this->sut->fetch('MyKey');

        self::assertEquals($expected, $actual);
    }
}
