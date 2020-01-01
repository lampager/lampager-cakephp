<?php

namespace Lampager\Cake;

use Cake\Collection\CollectionTrait;
use Cake\Datasource\ResultSetInterface;
use Iterator;
use IteratorAggregate;
use Lampager\PaginationResult as LampagerPaginationResult;

/**
 * Class PaginationResult
 *
 * This class intentionally does not extend \Lampager\PaginationResult
 * but has the same signature because \Cake\Datasource\ResultSetInterface
 * already implements \Iterator which conflicts with \IteratorAggregate.
 */
class PaginationResult implements ResultSetInterface
{
    /** @var LampagerPaginationResult */
    protected $result;

    /** @var Iterator */
    protected $iterator;

    use CollectionTrait;

    /**
     * PaginationResult constructor.
     * Merge $meta entries into $this.
     *
     * @param mixed $rows
     * @param array $meta
     */
    public function __construct($rows, array $meta)
    {
        $this->result = new LampagerPaginationResult($rows, $meta);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator->current();
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator->key();
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        $this->iterator->next();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->iterator = $this->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return (array)$this->result;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return json_encode($this->result);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        $obj = json_decode($serialized, true);
        $meta = $obj;
        unset($meta['records']);

        $this->result = new LampagerPaginationResult($obj['records'], $meta);
    }

    /**
     * @return Iterator
     */
    protected function getIterator()
    {
        /** @var Iterator|IteratorAggregate */
        $iterator = $this->result->getIterator();

        if ($iterator instanceof IteratorAggregate) {
            $iterator = $iterator->getIterator();
        }

        return $iterator;
    }
}
