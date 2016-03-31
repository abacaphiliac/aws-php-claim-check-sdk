<?php

namespace AbacaphiliacFeature\AwsSdk\ClaimCheck\Bootstrap\ContextTrait;

trait AwsConfigContextTrait
{
    /**
     * @return mixed[]
     */
    abstract public function overloadEnvironmentVariables();
    
    /**
     * @return mixed[]
     */
    public function getAwsServiceConfig()
    {
        $this->overloadEnvironmentVariables();
        
        return [
            'region' => getenv('AWS_REGION') ?: 'us-west-2',
            'version' => getenv('AWS_VERSION') ?: 'latest',
            'credentials' => [
                'key' => getenv('AWS_KEY') ?: 'foo',
                'secret' => getenv('AWS_SECRET') ?: 'bar',
            ],
        ];
    }
}
