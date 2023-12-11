<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\ORM\GraphQL\Filter;

use ApiSkeletons\Doctrine\ORM\GraphQL\Filter\Type\Between;
use ArchTech\Enums\InvokableCases;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Throwable;

use function array_map;
use function is_string;

enum Filters: string
{
    use InvokableCases;

    case EQ         = 'eq';
    case NEQ        = 'neq';
    case LT         = 'lt';
    case LTE        = 'lte';
    case GT         = 'gt';
    case GTE        = 'gte';
    case BETWEEN    = 'between';
    case CONTAINS   = 'contains';
    case STARTSWITH = 'startswith';
    case ENDSWITH   = 'endswith';
    case IN         = 'in';
    case NOTIN      = 'notin';
    case ISNULL     = 'isnull';
    case SORT       = 'sort';

    /**
     * Fetch the description for the filter
     */
    public function description(): string
    {
        return match ($this) {
            self::EQ         => 'Equals',
            self::NEQ        => 'Not equals',
            self::LT         => 'Less than',
            self::LTE        => 'Less than or equals',
            self::GT         => 'Greater than',
            self::GTE        => 'Greater than or equals',
            self::BETWEEN    => 'Is between from and to inclusive of from and to',
            self::CONTAINS   => 'Contains the value.  Strings only.',
            self::STARTSWITH => 'Starts with the value.  Strings only.',
            self::ENDSWITH   => 'Ends with the value.  Strings only.',
            self::IN         => 'In the array of values',
            self::NOTIN      => 'Not in the array of values',
            self::ISNULL     => 'Is null',
            self::SORT       => 'Sort by field. ASC or DESC.',
        };
    }

    /**
     * Fetch the GraphQL type for the filter
     */
    public function type(ScalarType|ListOfType $type): Type
    {
        try {
            return match ($this) {
                self::BETWEEN => new Between($type),
                self::IN => Type::listOf($type),
                self::ISNULL => Type::boolean(),
                self::NOTIN => Type::listOf($type),
                self::SORT => Type::string(),
            };
        } catch (Throwable) {
            return $type;
        }
    }

    /**
     * Convert an array of Filters or strings to an array of Filters
     *
     * @param array<string>|Filters[] $filters
     *
     * @return Filters[]
     */
    public static function fromArray(array $filters): array
    {
        $filters = array_map(
            static function ($filter) {
                return is_string($filter) ? Filters::from($filter) : $filter;
            },
            $filters,
        );

        return $filters;
    }
}
