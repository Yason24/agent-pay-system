<?php

namespace Yason\WebsiteTemplate\Core\Relations;

use Yason\WebsiteTemplate\Core\Model;

abstract class Relation
{
    protected Model $parent;
    protected string $related;

    public function __construct(Model $parent, string $related)
    {
        $this->parent = $parent;
        $this->related = $related;
    }

    public function getParent(): Model
    {
        return $this->parent;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    abstract public function getResults();
}