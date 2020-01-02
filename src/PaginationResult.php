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
        return $this->unwrap()->current();
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->unwrap()->key();
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->unwrap()->next();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->unwrap()->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return $this->unwrap()->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function unwrap()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray($preserveKeys = true)
    {
        $array = (array)$this->result;
        $array['records'] = iterator_to_array($this->unwrap());

        return $array;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return json_encode($this->jsonSerialize());
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

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            '(help)' => 'This is a Lampager Pagination Result object.',
        ] + $this->jsonSerialize();
    }
}
