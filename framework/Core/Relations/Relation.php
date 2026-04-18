<?php

namespace Framework\Core\Relations;

use Framework\Core\Model;

abstract class Relation
{
    protected Model $parent;
    protected string $related;

    public function __construct(Model $parent, string $related)
    {
        $this->parent = $parent;
        $this->related = $related;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    abstract public function getResults();
}