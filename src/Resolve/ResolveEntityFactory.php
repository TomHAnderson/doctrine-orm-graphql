<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\ORM\GraphQL\Resolve;

use ApiSkeletons\Doctrine\ORM\GraphQL\Config;
use ApiSkeletons\Doctrine\ORM\GraphQL\Event\FilterQueryBuilder as FilterQueryBuilderEvent;
use ApiSkeletons\Doctrine\ORM\GraphQL\Filter\QueryBuilder as FilterQueryBuilder;
use ApiSkeletons\Doctrine\ORM\GraphQL\Type\Entity;
use ArrayObject;
use Closure;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use GraphQL\Type\Definition\ResolveInfo;
use League\Event\EventDispatcher;

use function base64_decode;
use function base64_encode;

class ResolveEntityFactory
{
    public function __construct(
        protected Config $config,
        protected EntityManager $entityManager,
        protected EventDispatcher $eventDispatcher,
        protected ArrayObject $metadata,
    ) {
    }

    public function get(Entity $entity, string $eventName): Closure
    {
        return function ($objectValue, array $args, $context, ResolveInfo $info) use ($entity, $eventName) {
            $entityClass        = $entity->getEntityClass();
            $filterQueryBuilder = new FilterQueryBuilder();

            $doctrineQueryBuilder = $this->entityManager->createQueryBuilder();
            $doctrineQueryBuilder->select('entity')
                ->from($entityClass, 'entity');

            if (isset($args['filter'])) {
                $filterQueryBuilder->apply($args['filter'], $doctrineQueryBuilder);
            }

            return $this->buildPagination(
                entity: $entity,
                queryBuilder: $doctrineQueryBuilder,
                eventName: $eventName,
                objectValue: $objectValue,
                args: $args,
                context: $context,
                info: $info,
            );
        };
    }

    /** @return mixed[] */
    public function buildPagination(
        Entity $entity,
        QueryBuilder $queryBuilder,
        string $eventName,
        mixed ...$resolve,
    ): array {
        $paginationFields = [
            'first'  => 0,
            'last'   => 0,
            'before' => 0,
            'after'  => 0,
        ];

        if (isset($resolve['args']['pagination'])) {
            foreach ($resolve['args']['pagination'] as $field => $value) {
                switch ($field) {
                    case 'after':
                        $paginationFields[$field] = (int) base64_decode($value, true) + 1;
                        break;
                    case 'before':
                        $paginationFields[$field] = (int) base64_decode($value, true);
                        break;
                    default:
                        $paginationFields[$field] = $value;
                        break;
                }
            }
        }

        $offsetAndLimit = $this->calculateOffsetAndLimit($entity, $paginationFields);

        if ($offsetAndLimit['offset']) {
            $queryBuilder->setFirstResult($offsetAndLimit['offset']);
        }

        if ($offsetAndLimit['limit']) {
            $queryBuilder->setMaxResults($offsetAndLimit['limit']);
        }

        /**
         * Fire the event dispatcher using the passed event name.
         * Include all resolve variables.
         */
        $this->eventDispatcher->dispatch(
            new FilterQueryBuilderEvent(
                $queryBuilder,
                $eventName,
                ...$resolve,
            ),
        );

        $edgesAndCursors = $this->buildEdgesAndCursors($queryBuilder, $offsetAndLimit, $paginationFields);

        return [
            'edges' => $edgesAndCursors['edges'],
            'totalCount' => $edgesAndCursors['totalCount'],
            'pageInfo' => [
                'endCursor' => $edgesAndCursors['cursors']['end'],
                'startCursor' => $edgesAndCursors['cursors']['start'],
                'hasNextPage' => $edgesAndCursors['cursors']['end'] !== $edgesAndCursors['cursors']['last'],
                'hasPreviousPage' => $edgesAndCursors['cursors']['first'] !== null
                    && $edgesAndCursors['cursors']['start'] !== $edgesAndCursors['cursors']['first'],
            ],
        ];
    }

    /**
     * @param array<string, int> $offsetAndLimit
     * @param array<string, int> $paginationFields
     *
     * @return array<string, mixed>
     */
    protected function buildEdgesAndCursors(QueryBuilder $queryBuilder, array $offsetAndLimit, array $paginationFields): array
    {
        $index   = 0;
        $edges   = [];
        $cursors = [
            'start' => base64_encode((string) 0),
            'first' => null,
            'last'  => base64_encode((string) 0),
        ];

        $paginator = new Paginator($queryBuilder->getQuery());
        $itemCount = $paginator->count();

        // Rebuild paginator if needed
        if ($paginationFields['last'] && ! $paginationFields['before']) {
            $offsetAndLimit['offset'] = $itemCount - $paginationFields['last'];
            $queryBuilder->setFirstResult($offsetAndLimit['offset']);
            $paginator = new Paginator($queryBuilder->getQuery());
        }

        foreach ($paginator->getQuery()->getResult() as $result) {
            $cursors['last'] = base64_encode((string) ($index + $offsetAndLimit['offset']));

            $edges[] = [
                'node' => $result,
                'cursor' => $cursors['last'],
            ];

            if (! $cursors['first']) {
                $cursors['first'] = $cursors['last'];
            }

            $index++;
        }

        $endIndex       = $paginator->count() ? $paginator->count() - 1 : 0;
        $cursors['end'] = base64_encode((string) $endIndex);

        return [
            'cursors'    => $cursors,
            'edges'      => $edges,
            'totalCount' => $paginator->count(),
        ];
    }

    /**
     * @param array<string, int> $paginationFields
     *
     * @return array<string, int>
     */
    protected function calculateOffsetAndLimit(Entity $entity, array $paginationFields): array
    {
        $offset = 0;

        $limit = $this->metadata[$entity->getEntityClass()]['limit'];

        if (! $limit) {
            $limit = $this->config->getLimit();
        }

        $adjustedLimit = $paginationFields['first'] ?: $paginationFields['last'] ?: $limit;
        if ($adjustedLimit < $limit) {
            $limit = $adjustedLimit;
        }

        if ($paginationFields['after']) {
            $offset = $paginationFields['after'];
        } elseif ($paginationFields['before']) {
            $offset = $paginationFields['before'] - $limit;
        }

        if ($offset < 0) {
            $limit += $offset;
            $offset = 0;
        }

        return [
            'offset' => $offset,
            'limit'  => $limit,
        ];
    }
}
