<?php

declare(strict_types=1);

namespace Lampager\Cake;

use Cake\Datasource\Paging\PaginatedInterface;
use Countable;
use JsonSerializable;
use Lampager\PaginationResult as LampagerPaginationResult;

/**
 * Class PaginationResult
 */
class PaginationResult extends LampagerPaginationResult implements JsonSerializable, PaginatedInterface
{
    protected ?iterable $iterator = null;

    protected int $limit;

    /**
     * {@inheritdoc}
     */
    public function currentPage(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function perPage(): int
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function totalCount(): ?int
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function pageCount(): ?int
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPrevPage(): bool
    {
        return (bool)$this->hasPrevious;
    }

    /**
     * {@inheritdoc}
     */
    public function hasNextPage(): bool
    {
        return (bool)$this->hasNext;
    }

    /**
     * {@inheritdoc}
     */
    public function items(): iterable
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function pagingParam(string $name): mixed
    {
        return $this->pagingParams()[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function pagingParams(): array
    {
        return [
            'count' => $this->count(),
            'totalCount' => $this->totalCount(),
            'perPage' => $this->perPage(),
            'pageCount' => $this->pageCount(),
            'currentPage' => $this->currentPage(),
            'hasPrevPage' => $this->hasPrevPage(),
            'hasNextPage' => $this->hasNextPage(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $items = $this->items();
        $array = get_object_vars($this);
        $array['records'] = is_array($items) ? $items : iterator_to_array($items);
        unset($array['iterator']);
        unset($array['limit']);

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $items = $this->items();
        return $items instanceof Countable ? count($items) : iterator_count($items);
    }

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     */
    public function __debugInfo(): array
    {
        return [
            '(help)' => 'This is a Lampager Pagination Result object.',
        ] + $this->jsonSerialize();
    }
}
