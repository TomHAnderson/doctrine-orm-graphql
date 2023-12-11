<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\ORM\GraphQL\Attribute;

use ApiSkeletons\Doctrine\ORM\GraphQL\Filter\Filters;
use Exception;

use function in_array;

trait ExcludeFilters
{
    /** @return Filters[] */
    public function getExcludeFilters(): array
    {
        if ($this->includeFilters && $this->excludeFilters) {
            throw new Exception('includeFilters and excludeFilters are mutually exclusive.');
        }

        if ($this->includeFilters) {
            // Get a diff of the allowed filters and the excluded filters
            // array_diff does not work on enum
            foreach (Filters::cases() as $filter) {
                if (in_array($filter, $this->includeFilters)) {
                    continue;
                }

                $this->excludeFilters[] = $filter;
            }
        } elseif ($this->excludeFilters) {
            // Array intersect of the allowed filters and the excluded filters
            // array_intersect does not work on enum
            foreach (Filters::cases() as $filter) {
                if (! in_array($filter, $this->excludeFilters)) {
                    continue;
                }

                $this->excludeFilters[] = $filter;
            }
        }

        return $this->excludeFilters;
    }
}
