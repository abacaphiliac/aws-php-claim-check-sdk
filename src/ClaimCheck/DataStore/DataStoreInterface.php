<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\DataStore;

use Abacaphiliac\AwsSdk\ClaimCheck\Exception\ExceptionInterface;

interface DataStoreInterface
{
    /**
     * @param string $key
     * @return string
     * @throws ExceptionInterface
     */
    public function fetch($key);
    
    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function store($key, $value);
}
