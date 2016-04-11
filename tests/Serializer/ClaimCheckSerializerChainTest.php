<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\Serializer;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;
use Abacaphiliac\AwsSdk\ClaimCheck\Exception\InvalidArgumentException;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerChain;
use Abacaphiliac\AwsSdk\ClaimCheck\Serializer\ClaimCheckSerializerInterface;

class ClaimCheckSerializerChainTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|ClaimCheckSerializerInterface */
    private $firstSerializer;
    
    /** @var  \PHPUnit_Framework_MockObject_MockObject|ClaimCheckSerializerInterface */
    private $secondSerializer;
    
    /** @var ClaimCheckSerializerChain */
    private $sut;

    protected function setUp()
    {
        parent::setUp();

        $this->sut = new ClaimCheckSerializerChain([
            $this->firstSerializer = $this->getMock(ClaimCheckSerializerInterface::class),
            $this->secondSerializer = $this->getMock(ClaimCheckSerializerInterface::class),
        ]);
    }
    
    public function testSerializeViaFirstSerializer()
    {
        $claimCheck = new ClaimCheck('MyBucket', 'MyKey');

        $this->firstSerializer->method('serialize')
            ->willReturn($expected = 'Serialized!');

        $this->secondSerializer->method('serialize')
            ->willThrowException(new InvalidArgumentException());
        
        $actual = $this->sut->serialize($claimCheck);
        
        self::assertEquals($expected, $actual);
    }
    
    public function testSerializeViaSecondSerializer()
    {
        $claimCheck = new ClaimCheck('MyBucket', 'MyKey');

        $this->firstSerializer->method('serialize')
            ->willThrowException(new InvalidArgumentException());

        $this->secondSerializer->method('serialize')
            ->willReturn($expected = 'Serialized!');
        
        $actual = $this->sut->serialize($claimCheck);
        
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws ExceptionInterface
     * @expectedException \Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface
     */
    public function testNotSerialize()
    {
        $claimCheck = new ClaimCheck('MyBucket', 'MyKey');

        $this->firstSerializer->method('serialize')
            ->willThrowException(new InvalidArgumentException());

        $this->secondSerializer->method('serialize')
            ->willThrowException(new InvalidArgumentException());
        
        $this->sut->serialize($claimCheck);
    }
    
    public function testUnserializeViaFirstSerializer()
    {
        $this->firstSerializer->method('unserialize')
            ->willReturn($expected = new ClaimCheck('MyBucket', 'MyKey'));

        $this->secondSerializer->method('unserialize')
            ->willThrowException(new InvalidArgumentException());
        
        $actual = $this->sut->unserialize('Serialized!');
        
        self::assertEquals($expected, $actual);
    }
    
    public function testUnserializeViaSecondSerializer()
    {
        $this->firstSerializer->method('unserialize')
            ->willThrowException(new InvalidArgumentException());

        $this->secondSerializer->method('unserialize')
            ->willReturn($expected = new ClaimCheck('MyBucket', 'MyKey'));
        
        $actual = $this->sut->unserialize('Serialized!');
        
        self::assertEquals($expected, $actual);
    }

    /**
     * @throws ExceptionInterface
     * @expectedException \Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface
     */
    public function testNotUnserialize()
    {
        $this->firstSerializer->method('unserialize')
            ->willThrowException(new InvalidArgumentException());

        $this->secondSerializer->method('unserialize')
            ->willThrowException(new InvalidArgumentException());
        
        $this->sut->unserialize('Serialized!');
    }
}
