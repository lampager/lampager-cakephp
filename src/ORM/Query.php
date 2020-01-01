<?php

namespace Lampager\Cake\ORM;

use Cake\Database\Expression\OrderByExpression;
use Cake\Database\Expression\OrderClauseExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\ORM\Query as BaseQuery;
use Lampager\Ckae\PaginationResult;
use Lampager\Cake\Paginator;
use Lampager\Contracts\Cursor;

/**
 * @method $this forward(bool $forward = true) Define that the current pagination is going forward.
 * @method $this backward(bool $backward = true) Define that the current pagination is going backward.
 * @method $this exclusive(bool $exclusive = true) Define that the cursor value is not included in the previous/next result.
 * @method $this inclusive(bool $inclusive = true) Define that the cursor value is included in the previous/next result.
 * @method $this seekable(bool $seekable = true) Define that the query can detect both "has_previous" and "has_next".
 * @method $this unseekable(bool $unseekable = true) Define that the query can detect only either "has_previous" or "has_next".
 * @method $this fromArray(mixed[] $options) Define options from an associative array.
 */
class Query extends BaseQuery
{
    /** @var Paginator */
    protected $_paginator;

    /** @var Cursor|int[]|string[] */
    protected $_cursor = [];

    /**
     * Construct query.
     *
     * @param \Cake\Database\Connection $connection The connection object
     * @param \Cake\ORM\Table           $table      The table this query is starting on
     */
    public function __construct($connection, $table)
    {
        parent::__construct($connection, $table);

        $this->_paginator = Paginator::create($this);
    }

    /**
     * Create query based on the existing query. This factory copies the internal
     * state of the given query to a new instance.
     *
     * @param BaseQuery $query
     */
    public static function fromQuery($query)
    {
        $obj = new static($query->getConnection(), $query->getRepository());

        foreach (get_object_vars($query) as $k => $v) {
            $obj->$k = $v;
        }

        $obj->_executeOrder($obj->clause('order'));
        $obj->_executeLimit($obj->clause('limit'));

        return $obj;
    }

    /**
     * Set the cursor.
     *
     * @param Cursor[]|int[]|string[] $cursor
     */
    public function cursor($cursor = [])
    {
        $this->_cursor = $cursor;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function order($fields, $overwrite = false)
    {
        parent::order($fields, $overwrite);
        $this->_executeOrder($this->clause('order'));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderAsc($field, $overwrite = false)
    {
        parent::orderAsc($field, $overwrite);
        $this->_executeOrder($this->clause('order'));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderDesc($field, $overwrite = false)
    {
        parent::orderDesc($field, $overwrite);
        $this->_executeOrder($this->clause('order'));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function limit($num)
    {
        parent::limit($num);
        $this->_executeLimit($this->clause('limit'));

        return $this;
    }

    /**
     * {@inheritDoc}
     * @return PaginationResult
     */
    public function all()
    {
        return $this->_paginator->paginate($this->_cursor);
    }

    /**
     * {@inheritDoc}
     */
    protected function _performCount()
    {
        return $this->_paginator->build($this->_cursor)->_performCount();
    }

    /**
     * @param  null|OrderByExpression $order
     * @return void
     */
    protected function _executeOrder($order)
    {
        $this->_paginator->clearOrderBy();

        if ($order === null) {
            return;
        }

        $generator = $this->getValueBinder();
        $order->iterateParts(function ($condition, $key) use ($generator) {
            if (!is_int($key)) {
                /**
                 * @var string $key       The column
                 * @var string $condition The order
                 */
                $this->_paginator->orderBy($key, $condition);
            }

            if ($condition instanceof OrderClauseExpression) {
                $generator->resetCount();

                if (!preg_match('/ (?<direction>ASC|DESC)$/', $condition->sql($generator), $matches)) {
                    throw new \LogicException('OrderClauseExpression does not have direction');
                }

                /** @var string $direction */
                $direction = $matches['direction'];
                $field = $condition->getField();

                if ($field instanceof ExpressionInterface) {
                    $generator->resetCount();
                    $this->_paginator->orderBy($field->sql($generator), $direction);
                } else {
                    $this->_paginator->orderBy($field, $direction);
                }
            }

            if ($condition instanceof QueryExpression) {
                $generator->resetCount();
                $this->_paginator->orderBy($condition->sql($generator));
            }

            return $condition;
        });
    }

    /**
     * @param  null|int|QueryExpression $limit
     * @return void
     */
    protected function _executeLimit($limit)
    {
        if (is_int($limit)) {
            $this->_paginator->limit($limit);
            return;
        }

        if ($limit instanceof QueryExpression) {
            $generator = $this->getValueBinder();
            $generator->resetCount();
            $this->_paginator->limit($limit->sql($generator));
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __call($method, $args)
    {
        static $options = [
            'forward',
            'backward',
            'exclusive',
            'inclusive',
            'seekable',
            'unseekable',
            'fromArray',
        ];

        if (in_array($method, $options, true)) {
            $this->_paginator->$method(...$args);
            return $this;
        }

        return parent::__call($method, $args);
    }

    /**
     * {@inheritDoc}
     */
    public function __debugInfo()
    {
        return [
            '(help)' => 'This is a Lampager Query object to get the paginated results.',
            'paginator' => $this->_paginator,
        ] + $this->_paginator->build($this->_cursor)->__debugInfo();
    }
}
