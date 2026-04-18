<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;

class QueryBuilder
{
    private string $table;
    private PDO $db;

    private array $wheres = [];
    private array $bindings = [];

    private ?string $orderBy = null;
    private ?int $limit = null;

    private string $modelClass;

    private array $with = [];

    public function __construct(string $table, string $modelClass)
    {
        $this->db = (new Database())->getConnection();
        $this->table = $table;
        $this->modelClass = $modelClass;
    }

    /*
    |--------------------------------------------------------------------------
    | WHERE
    |--------------------------------------------------------------------------
    */

    public function where(string $column, string $operator, $value): self
    {
        $param = $column . count($this->bindings);

        $this->wheres[] = "$column $operator :$param";
        $this->bindings[$param] = $value;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER BY
    |--------------------------------------------------------------------------
    */

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException("Invalid orderBy column.");
        }

        if (!empty($this->modelClass::$sortable)
            && !in_array($column, $this->modelClass::$sortable, true)) {
            throw new \InvalidArgumentException("Column not sortable.");
        }

        $this->orderBy = "{$column} {$direction}";

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMIT
    |--------------------------------------------------------------------------
    */

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD SQL
    |--------------------------------------------------------------------------
    */

    private function buildSql(): string
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        return $sql;
    }

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    public function get(): Collection
    {
        $stmt = $this->db->prepare($this->buildSql());
        $stmt->execute($this->bindings);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $models = array_map(
            fn($row) => new $this->modelClass($row),
            $rows
        );

        $this->eagerLoad($models);

        return new Collection($models);
    }

    /*
    |--------------------------------------------------------------------------
    | FIRST
    |--------------------------------------------------------------------------
    */

    public function first()
    {
        return $this->limit(1)->get()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | with
    |--------------------------------------------------------------------------
    */
    public function with(string|array $relations): self
    {
        $this->with = (array)$relations;
        return $this;
    }

    private function eagerLoad(array $models): void
    {
        if (!$this->with || !$models) {
            return;
        }

        $model = new $this->modelClass;

        foreach ($this->with as $relation) {

            if (!method_exists($model, $relation)) {
                continue;
            }

            $relationObject = $model->$relation();

            $foreignKey = $relationObject->getForeignKey();
            $ownerKey   = $relationObject->getOwnerKey();
            $related    = $relationObject->getRelated();

            $ids = array_unique(
                array_map(fn($m) => $m->$foreignKey, $models)
            );

            if (!$ids) continue;

            $relatedModels = $related::whereIn($ownerKey, $ids);

            $dictionary = [];

            foreach ($relatedModels as $relModel) {
                $dictionary[$relModel->$ownerKey] = $relModel;
            }

            foreach ($models as $modelInstance) {
                $key = $modelInstance->$foreignKey;
                $modelInstance->setRelation($relation, $dictionary[$key] ?? null);
            }
        }
    }
}