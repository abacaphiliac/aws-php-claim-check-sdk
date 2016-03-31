<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

use Dotenv\Dotenv;

trait DotEnvContextTrait
{
    /**
     * @return mixed[]
     */
    public function loadEnvironmentVariables()
    {
        return $this->createDotEnv()->load();
    }

    /**
     * @return mixed[]
     */
    public function overloadEnvironmentVariables()
    {
        return $this->createDotEnv()->overload();
    }

    /**
     * @return Dotenv
     */
    private function createDotEnv()
    {
        return new Dotenv(__DIR__ . '/../../../', '.env');
    }
}
