<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Field
{
    protected string $group;

    protected ?string $strategy;

    protected ?string $docs;

    public function __construct(
        string $group = 'default',
        ?string $strategy = null,
        ?string $docs = null
    ) {
        $this->group = $group;
        $this->strategy = $strategy;
        $this->docs = $docs;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    public function getDocs(): ?string
    {
        return $this->docs;
    }
}
