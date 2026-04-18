<?php

namespace Yason\WebsiteTemplate\Core;

class Collection implements \IteratorAggregate, \Countable
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function first()
    {
        return $this->items[0] ?? null;
    }

    public function last()
    {
        return end($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function map(callable $callback): static
    {
        return new static(
            array_map($callback, $this->items)
        );
    }

    public function filter(callable $callback): static
    {
        return new static(
            array_values(array_filter($this->items, $callback))
        );
    }

    public function pluck(string $key): array
    {
        return array_map(
            fn($item) => $item->$key,
            $this->items
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }
}