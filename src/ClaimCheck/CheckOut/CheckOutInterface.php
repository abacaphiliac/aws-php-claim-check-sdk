<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\CheckOut;

use Abacaphiliac\AwsSdk\ClaimCheck\Message;

interface CheckOutInterface
{
    /**
     * @return Message
     */
    public function fetch();
}
