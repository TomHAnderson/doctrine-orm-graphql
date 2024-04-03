<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\ORM\GraphQL\Attribute;

use ApiSkeletons\Doctrine\ORM\GraphQL\Filter\Filters;
use Attribute;

/**
 * Attribute to describe an association for GraphQL
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Association
{
    use ExcludeFilters;

    /**
     * @param Filters[] $excludeFilters
     * @param Filters[] $includeFilters
     */
    public function __construct(
        protected string $alias = '',
        protected string|null $criteriaEventName = null,
        protected string|null $description = null,
        array $excludeFilters = [],
        protected string $group = 'default',
        protected string|null $hydratorStrategy = null,
        array $includeFilters = [],
        protected int|null $limit = null,
    ) {
        $this->includeFilters = $includeFilters;
        $this->excludeFilters = $excludeFilters;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getLimit(): int|null
    {
        return $this->limit;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getHydratorStrategy(): string|null
    {
        return $this->hydratorStrategy;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getCriteriaEventName(): string|null
    {
        return $this->criteriaEventName;
    }
}
