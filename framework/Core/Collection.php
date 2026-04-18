<?php

namespace Framework\Core;

use ArrayIterator;
use IteratorAggregate;
use Countable;

class Collection implements IteratorAggregate, Countable
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /*
    |--------------------------------------------------------------------------
    | BASE
    |--------------------------------------------------------------------------
    */

    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /*
    |--------------------------------------------------------------------------
    | MAP
    |--------------------------------------------------------------------------
    */

    public function map(callable $callback): self
    {
        return new static(
            array_map($callback, $this->items)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    public function filter(callable $callback): self
    {
        return new static(
            array_values(array_filter($this->items, $callback))
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EACH
    |--------------------------------------------------------------------------
    */

    public function each(callable $callback): self
    {
        foreach ($this->items as $item) {
            $callback($item);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | FIRST
    |--------------------------------------------------------------------------
    */

    public function first()
    {
        return $this->items[0] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | FIRST WHERE
    |--------------------------------------------------------------------------
    */

    public function firstWhere(string $key, $value)
    {
        foreach ($this->items as $item) {
            if ($item->$key == $value) {
                return $item;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | PLUCK
    |--------------------------------------------------------------------------
    */

    public function pluck(string $key): self
    {
        return new static(
            array_map(fn($item) => $item->$key, $this->items)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | KEY BY
    |--------------------------------------------------------------------------
    */

    public function keyBy(string $key): self
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[$item->$key] = $item;
        }

        return new static($result);
    }

    /*
    |--------------------------------------------------------------------------
    | GROUP BY
    |--------------------------------------------------------------------------
    */

    public function groupBy(string $key): self
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[$item->$key][] = $item;
        }

        return new static($result);
    }

    /*
    |--------------------------------------------------------------------------
    | TAP
    |--------------------------------------------------------------------------
    */

    public function tap(callable $callback): self
    {
        $callback($this);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | DD
    |--------------------------------------------------------------------------
    */

    public function dd(): never
    {
        dd($this->items);
    }
}