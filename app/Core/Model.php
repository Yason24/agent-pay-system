<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;

use Yason\WebsiteTemplate\Core\Relations\BelongsTo;

use Yason\WebsiteTemplate\Core\Collection;

abstract class Model
{
    protected static string $table;

    protected array $attributes = [];

    protected array $relations = [];

    protected static array $sortable = [];

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

        // relation cache
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // lazy relation load
        if (method_exists($this, $key)) {

            $relation = $this->$key()->getResults();

            $this->relations[$key] = $relation;

            return $relation;
        }

        return null;
    }

    public function __set($key, $value)
    {
        if ($value instanceof self || is_array($value)) {
            $this->relations[$key] = $value;
            return;
        }

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
    | first
    |--------------------------------------------------------------------------
    */
    public static function first()
    {
        return static::query()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | BELONGS TO
    |--------------------------------------------------------------------------
    */

    public function belongsTo(string $related, string $foreignKey)
    {
        return new BelongsTo(
            $this,
            $related,
            $foreignKey
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HAS MANY
    |--------------------------------------------------------------------------
    */

    public function hasMany(string $related, string $foreignKey)
    {
        return new HasMany(
            $this,
            $related,
            $foreignKey
        );
    }

    /*
    |--------------------------------------------------------------------------
    | with
    |--------------------------------------------------------------------------
    */
    /*protected static array $with = [];*/

    public static function with(string|array $relations): QueryBuilder
    {
        return static::query()->with($relations);
    }

    /*
    |--------------------------------------------------------------------------
    | Eager Loader Engine
    |--------------------------------------------------------------------------
    */
    public static function whereIn($column, array $values)
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));

        $sql = "SELECT * FROM " . static::$table .
            " WHERE $column IN ($placeholders)";

        $db = (new Database())->getConnection();

        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        return array_map(
            fn($row) => new static($row),
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    public static function get(): Collection
    {
        return static::query()->get();
    }

    public function setRelation(string $key, $value): void
    {
        $this->relations[$key] = $value;
    }
}