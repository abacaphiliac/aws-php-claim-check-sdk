<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\CheckIn;

use Abacaphiliac\AwsSdk\ClaimCheck\Message;

interface CheckInInterface
{
    /**
     * @param Message $message
     * @return Message
     */
    public function store(Message $message);
}
