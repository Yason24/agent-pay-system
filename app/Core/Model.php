<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;

abstract class Model
{
    protected static string $table;

    protected array $attributes = [];

    protected PDO $db;

    public function __construct(array $attributes = [])
    {
        $this->db = (new Database())->getConnection();
        $this->attributes = $attributes;
    }

    /*
    |--------------------------------------------------------------------------
    | MAGIC GET/SET
    |--------------------------------------------------------------------------
    */

    public function __get($key)
    {
        // attribute
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        // relation
        if (method_exists($this, $key)) {
            return $this->$key();
        }

        return null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public static function create(array $data): static
    {
        $instance = new static();

        $db = (new Database())->getConnection();

        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));

        $stmt = $db->prepare(
            "INSERT INTO " . static::$table .
            " ($columns) VALUES ($placeholders)"
        );

        $stmt->execute($data);

        $data['id'] = $db->lastInsertId();

        return new static($data);
    }

    /*
    |--------------------------------------------------------------------------
    | FIND
    |--------------------------------------------------------------------------
    */

    public static function find(int $id): ?static
    {
        $db = (new Database())->getConnection();

        $stmt = $db->prepare(
            "SELECT * FROM " . static::$table . " WHERE id = :id"
        );

        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new static($data) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | ALL
    |--------------------------------------------------------------------------
    */

    public static function all(): array
    {
        $db = (new Database())->getConnection();

        $stmt = $db->query("SELECT * FROM " . static::$table);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new static($row), $rows);
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE (UPDATE)
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        if (!isset($this->attributes['id'])) {
            return;
        }

        $id = $this->attributes['id'];

        $columns = [];

        foreach ($this->attributes as $key => $value) {
            if ($key === 'id') continue;
            $columns[] = "$key = :$key";
        }

        $sql = "UPDATE " . static::$table .
            " SET " . implode(',', $columns) .
            " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM " . static::$table . " WHERE id = :id"
        );

        $stmt->execute([
            'id' => $this->attributes['id']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Builder
    |--------------------------------------------------------------------------
    */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::$table, static::class);
    }

    /*
    |--------------------------------------------------------------------------
    | where
    |--------------------------------------------------------------------------
    */
    public static function where($column,$operator,$value): QueryBuilder
    {
        return static::query()->where($column,$operator,$value);
    }

    /*
    |--------------------------------------------------------------------------
    | get
    |--------------------------------------------------------------------------
    */
    public static function get(): array
    {
        return static::query()->get(static::class);
    }

    /*
    |--------------------------------------------------------------------------
    | first
    |--------------------------------------------------------------------------
    */
    public static function first()
    {
        return static::query()->first(static::class);
    }

    /*
    |--------------------------------------------------------------------------
    | BELONGS TO
    |--------------------------------------------------------------------------
    */

    public function belongsTo(string $related, string $foreignKey)
    {
        $relatedModel = new $related();

        return $related::where(
            'id',
            '=',
            $this->$foreignKey
        )->first();
    }

    /*
    |--------------------------------------------------------------------------
    | HAS MANY
    |--------------------------------------------------------------------------
    */

    public function hasMany(string $related, string $foreignKey)
    {
        return $related::where(
            $foreignKey,
            '=',
            $this->id
        )->get();
    }
}