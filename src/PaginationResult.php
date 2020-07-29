<?php

declare(strict_types=1);

namespace Lampager\Cake;

use Cake\Collection\CollectionTrait;
use Cake\Datasource\ResultSetInterface;
use Iterator;
use IteratorAggregate;
use Lampager\PaginationResult as LampagerPaginationResult;
use Traversable;

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
     * @return Iterator
     */
    public function unwrap(): Traversable
    {
        if (!$this->iterator) {
            $this->iterator = $this->getIterator();
        }

        return $this->iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(bool $preserveKeys = true): array
    {
        $array = (array)$this->result;
        $array['records'] = iterator_to_array($this->unwrap());

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
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

    protected function getIterator(): Iterator
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
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this->result, $name)) {
            return $this->result->{$name};
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );

        return null;
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
