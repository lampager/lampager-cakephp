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
 *
 * @property-read mixed      $records
 * @property-read null|bool  $hasPrevious
 * @property-read null|mixed $previousCursor
 * @property-read null|bool  $hasNext
 * @property-read null|mixed $nextCursor
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
     */
    public function __construct($rows, array $meta)
    {
        $this->result = new LampagerPaginationResult($rows, $meta);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->unwrap()->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->unwrap()->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->unwrap()->next();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->unwrap()->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->unwrap()->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function unwrap()
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($preserveKeys = true)
    {
        $array = (array)$this->result;
        $array['records'] = iterator_to_array($this->unwrap());

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * {@inheritdoc}
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
     * @param string $name The name of the parameter to fetch
     */
    public function __get($name)
    {
        if (property_exists($this->result, $name)) {
            return $this->result->{$name};
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );
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
