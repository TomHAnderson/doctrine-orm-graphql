<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Return the same value
 */
class FieldDefault implements
    StrategyInterface,
    Invokable
{
    public function extract($value, ?object $object = null)
    {
        return $value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value, ?array $data)
    {
        return $value;
    }
}
