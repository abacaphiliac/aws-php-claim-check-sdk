<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap;

use AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait\AwsConfigContextTrait;
use AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait\DotEnvContextTrait;
use AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait\S3ContextTrait;
use AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait\SnsContextTrait;
use AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait\SqsContextTrait;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use RandomLib\Factory;

/**
 * Defines application features from the specific context.
 */
class S3DataStoreContext implements Context, SnippetAcceptingContext
{
    use AwsConfigContextTrait,
        DotEnvContextTrait,
        S3ContextTrait,
        SqsContextTrait,
        SnsContextTrait;
    
    /** @var  int */
    private $largeMessageLength = 512000;
    
    /** @var  string */
    private $largeMessage;
    
    /** @var  string */
    private $message;

    /**
     * @Given /^I have a large message$/
     */
    public function iHaveLargeMessage()
    {
        $this->message = $this->getLargeMessage();
    }

    /**
     * @Given /^I have a small message$/
     */
    public function iHaveASmallMessage()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I have a small message that contains PHI$/
     */
    public function iHaveASmallMessageThatContainsPhi()
    {
        throw new PendingException();
    }

    /**
     * @return string
     */
    public function getLargeMessage()
    {
        if (!$this->largeMessage) {
            $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(__METHOD__);
            if (file_exists($filename)) {
                // TODO Check file content length.
                
                return file_get_contents($filename);
            }
            
            $factory = new Factory();
            $generator = $factory->getLowStrengthGenerator();
            $this->largeMessage = $generator->generateString($this->largeMessageLength);
            
            file_put_contents($filename, $this->largeMessage);
        }
        
        return $this->largeMessage;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
