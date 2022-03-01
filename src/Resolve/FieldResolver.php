<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use Doctrine\Common\Util\ClassUtils;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;

use function is_array;
use function spl_object_hash;

/**
 * A field resolver that uses the Doctrine Laminas hydrator.
 */
class FieldResolver
{
    /**
     * Cache all hydrator extract operations based on spl object hash
     *
     * @var mixed[]
     */
    private array $extractValues = [];

    protected Config $config;

    protected Metadata $metadata;

    public function __construct(Config $config, Metadata $metadata)
    {
        $this->config   = $config;
        $this->metadata = $metadata;
    }

    /**
     * @throws Error
     */
    public function __invoke(mixed $source, mixed $args, mixed $context, ResolveInfo $info): mixed
    {
        if (is_array($source)) {
            return $source[$info->fieldName];
        }

        $entityClass   = ClassUtils::getRealClass($source::class);
        $splObjectHash = spl_object_hash($source);

        $hydrator = $this->metadata->get($entityClass)->getHydrator();

        /**
         * For disabled hydrator cache, store only last hydrator result and reuse for consecutive calls
         * then drop the cache if it doesn't hit.
         */
        if (! $this->config->getUseHydratorCache()) {
            if (isset($this->extractValues[$splObjectHash])) {
                return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
            } else {
                $this->extractValues = [];
            }

            $this->extractValues[$splObjectHash] = $hydrator->extract($source);

            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        // Use full hydrator cache
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        $this->extractValues[$splObjectHash] = $hydrator->extract($source);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
