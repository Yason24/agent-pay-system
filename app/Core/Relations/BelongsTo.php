<?php

namespace Yason\WebsiteTemplate\Core\Relations;

use Yason\WebsiteTemplate\Core\Model;

class BelongsTo
{
    protected Model $parent;
    protected string $related;
    protected string $foreignKey;
    protected string $ownerKey = 'id';

    public function __construct(
        Model $parent,
        string $related,
        string $foreignKey
    ) {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
    }

    public function getResults()
    {
        return $this->related::where(
            $this->ownerKey,
            '=',
            $this->parent->{$this->foreignKey}
        )->first();
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    public function getOwnerKey()
    {
        return $this->ownerKey;
    }

    public function getRelated()
    {
        return $this->related;
    }
}