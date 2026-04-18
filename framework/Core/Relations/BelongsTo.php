<?php

namespace Framework\Core\Relations;

use Framework\Core\Model;

class BelongsTo extends Relation
{
    protected string $foreignKey;
    protected string $ownerKey;

    public function __construct(
        Model $parent,
        string $related,
        string $foreignKey,
        string $ownerKey = 'id'
    ) {
        parent::__construct($parent, $related);

        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    public function getOwnerKey()
    {
        return $this->ownerKey;
    }

    public function getResults()
    {
        $related = $this->related;

        return $related::where(
            $this->ownerKey,
            '=',
            $this->parent->{$this->foreignKey}
        )->first();
    }
}