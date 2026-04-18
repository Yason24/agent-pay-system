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
        $this->orderBy = "$column $direction";
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

    public function get(): array
    {
        $stmt = $this->db->prepare($this->buildSql());
        $stmt->execute($this->bindings);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn($row) => new $this->modelClass($row),
            $rows
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FIRST
    |--------------------------------------------------------------------------
    */

    public function first()
    {
        $this->limit(1);

        $result = $this->get();

        return $result[0] ?? null;
    }
}