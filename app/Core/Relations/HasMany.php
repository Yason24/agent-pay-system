<?php

namespace Yason\WebsiteTemplate\Core\Relations;

use Yason\WebsiteTemplate\Core\Model;

class HasMany extends Relation
{
    protected string $foreignKey;
    protected string $localKey;

    public function __construct(
        Model $parent,
        string $related,
        string $foreignKey,
        string $localKey = 'id'
    ) {
        parent::__construct($parent, $related);

        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function getResults()
    {
        $related = $this->related;

        return $related::where(
            $this->foreignKey,
            '=',
            $this->parent->{$this->localKey}
        )->get();
    }
}