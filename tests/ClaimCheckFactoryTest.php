<?php

namespace AbacaphiliacTest\AwsSdk\ClaimCheck\ClaimCheck;

use Abacaphiliac\AwsSdk\ClaimCheck\ClaimCheckFactory;

class ClaimCheckFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateClaimCheck()
    {
        $sut = new ClaimCheckFactory($expected = 'MyBucket');
        
        $actual = $sut->create();
        
        self::assertEquals($expected, $actual->getS3BucketName());
        self::assertNotEmpty($actual->getS3Key());
    }
}
