<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck;

class Message
{
    /** @var  string */
    private $content;
    
    /** @var  mixed[] */
    private $attributes = [];

    /** @var  ClaimCheck */
    private $claimCheck;

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed[] $attributes
     */
    public function setAttributes(array $attributes = [])
    {
        $this->attributes = [];
        
        array_walk($attributes, [$this, 'addAttribute']);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @return ClaimCheck
     */
    public function getClaimCheck()
    {
        return $this->claimCheck;
    }

    /**
     * @param ClaimCheck $claimCheck
     */
    public function setClaimCheck($claimCheck)
    {
        $this->claimCheck = $claimCheck;
    }
}
