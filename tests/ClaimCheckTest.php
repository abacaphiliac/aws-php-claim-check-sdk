<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\ClaimCheck;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheck;

class ClaimCheckTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBucket()
    {
        $sut = new ClaimCheck($expected = 'MyBucket');
        
        self::assertEquals($expected, $sut->getS3BucketName());
    }
    
    public function testGetDefaultKey()
    {
        $sut = new ClaimCheck('MyBucket');
        
        self::assertNotEmpty($sut->getS3Key());
    }
    
    public function testGetKey()
    {
        $sut = new ClaimCheck('MyBucket', $expected = 'MyKey');
        
        self::assertEquals($expected, $sut->getS3Key());
    }
}
