<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Hydrator;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\TypeTest;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class DataTypesTest extends AbstractTest
{
    public function testDataTypes(): void
    {
        $config = new Config([
            'group' => 'DataTypesTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

//        print_r($driver->get(Metadata::class)->getMetadataConfig());die();

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'typetest' => [
                        'type' => Type::listOf($driver->type(TypeTest::class)),
                        'args' => [
                            'filter' => $driver->filter(TypeTest::class),
                        ],
                        'resolve' => $driver->resolve(TypeTest::class),
                    ],
                ],
            ]),
        ]);

        $query = '{ typetest { testInt testDateTime testFloat testBool testText testArray } }';

        $result = GraphQL::executeQuery($schema, $query);
        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['typetest']));
    }
}
