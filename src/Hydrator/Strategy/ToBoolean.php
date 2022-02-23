<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Transform a value into a php native boolean
 *
 * @returns float
 */
class ToBoolean implements
    StrategyInterface,
    Invokable
{
    public function extract($value, ?object $object = null)
    {
        if (is_null($value)) {
            return $value;
        }

        return (bool)$value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value, ?array $data)
    {
        if (is_null($value)) {
            return $value;
        }

        return (bool)$value;
    }
}
