<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\ORM\GraphQL\Feature\Hydrator;

use ApiSkeletons\Doctrine\ORM\GraphQL\Config;
use ApiSkeletons\Doctrine\ORM\GraphQL\Driver;
use ApiSkeletons\Doctrine\ORM\GraphQL\Type\Entity\EntityTypeContainer;
use ApiSkeletonsTest\Doctrine\ORM\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\ORM\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\ORM\GraphQL\Entity\User;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

class AliasMapTest extends AbstractTest
{
    public function testNamingStrategy(): void
    {
        $config = new Config(['group' => 'AliasMapTest']);

        $driver = new Driver($this->getEntityManager(), $config);

        $artistEntityType = $driver->get(EntityTypeContainer::class)->get(Artist::class);

        $this->assertIsArray($artistEntityType->getAliasMap());

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => $driver->connection(Artist::class),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query = '
          {
            artist {
              edges {
                node {
                  title
                  gigs {
                    edges {
                      node {
                        id
                      }
                    }
                  }
                }
              }
            }
          }
        ';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        print_r($output);die();
    }
}
