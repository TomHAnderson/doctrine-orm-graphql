<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Type\DateTime as DateTimeType;
use GraphQL\Type\Definition\Type;

class TypeManager extends AbstractContainer
{
    public function __construct()
    {
        $this
            ->set('tinyint', Type::int())
            ->set('smallint', Type::int())
            ->set('integer', Type::int())
            ->set('int', Type::int())
            ->set('boolean', Type::boolean())
            ->set('decimal', Type::float())
            ->set('float', Type::float())
            ->set('bigint', Type::string())
            ->set('string', Type::string())
            ->set('text', Type::string())
            ->set('array', Type::listOf(Type::string()))
            ->set('datetime', new DateTimeType())
            ;
    }
}
