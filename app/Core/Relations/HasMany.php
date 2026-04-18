<?php

namespace Yason\WebsiteTemplate\Core\Relations;

class HasMany extends Relation
{
    protected string $foreignKey;
    protected string $localKey = 'id';

    public function __construct($parent, string $related, string $foreignKey)
    {
        parent::__construct($parent, $related);

        $this->foreignKey = $foreignKey;
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