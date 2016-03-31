<?php

namespace Abacaphiliac\AwsSdk\ClaimCheck\Transformer;

class MessageAttributesTransformer
{
    /**
     * @param string[] $attributes
     * @return array[]
     */
    public static function transformToMessageArgs(array $attributes = [])
    {
        // Strip null and empty-string.
        $attributes = array_filter($attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        
        return array_map(function ($value, $key) {
            return [
                'Name' => $key,
                'StringValue' => $value,
                'DataType' => 'String',
            ];
        }, $attributes);
    }
}
