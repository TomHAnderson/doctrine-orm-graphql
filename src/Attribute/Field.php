<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\ORM\GraphQL\Attribute;

use ApiSkeletons\Doctrine\ORM\GraphQL\Filter\Filters;
use Attribute;

/**
 * Attribute to describe a field for GraphQL
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Field
{
    use ExcludeFilters;

    /**
     * @param Filters[] $excludeFilters
     * @param Filters[] $includeFilters
     */
    public function __construct(
        protected string|null $alias = null,
        protected string|null $description = null,
        array $excludeFilters = [],
        protected string $group = 'default',
        protected string|null $hydratorStrategy = null,
        array $includeFilters = [],
        protected string|null $type = null,
    ) {
        $this->includeFilters = $includeFilters;
        $this->excludeFilters = $excludeFilters;
    }

    public function getAlias(): string|null
    {
        return $this->alias;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getHydratorStrategy(): string|null
    {
        return $this->hydratorStrategy;
    }

    public function getType(): string|null
    {
        return $this->type;
    }
}
