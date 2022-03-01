<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Event;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use Doctrine\ORM\QueryBuilder;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class FilterQueryBuilderTest extends AbstractTest
{
    public function testEvent()
    {
        $driver = new Driver($this->getEntityManager());
        $driver->getEventDispatcher()->subscribeTo('filter.querybuilder',
            function(FilterQueryBuilder $event) {
                $this->assertInstanceOf(QueryBuilder::class, $event->getQueryBuilder());
                $this->assertEquals([
                        'ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist' => 'entity'
                ], $event->getEntityAliasMap());
            }
        );

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => Type::listOf($driver->type(Artist::class)),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query = '{
            artist (filter: { name_contains: "dead" })
                { id name performances { venue recordings { source } } }
        }';

        GraphQL::executeQuery($schema, $query);
    }
}
