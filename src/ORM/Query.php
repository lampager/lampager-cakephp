<?php

declare(strict_types=1);

namespace Lampager\Cake\ORM;

use Cake\Database\Expression\OrderByExpression;
use Cake\Database\Expression\OrderClauseExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\Table;
use Lampager\Cake\Paginator;
use Lampager\Contracts\Cursor;
use Lampager\Exceptions\Query\BadKeywordException;
use Lampager\Exceptions\Query\InsufficientConstraintsException;
use Lampager\Exceptions\Query\LimitParameterException;

/**
 * @method $this forward(bool $forward = true)       Define that the current pagination is going forward.
 * @method $this backward(bool $backward = true)     Define that the current pagination is going backward.
 * @method $this exclusive(bool $exclusive = true)   Define that the cursor value is not included in the previous/next result.
 * @method $this inclusive(bool $inclusive = true)   Define that the cursor value is included in the previous/next result.
 * @method $this seekable(bool $seekable = true)     Define that the query can detect both "has_previous" and "has_next".
 * @method $this unseekable(bool $unseekable = true) Define that the query can detect only either "has_previous" or "has_next".
 * @method $this fromArray(mixed[] $options)         Define options from an associative array.
 */
class Query extends SelectQuery
{
    /** @var Paginator */
    protected $_paginator;

    /** @var Cursor|int[]|string[] */
    protected $_cursor = [];

    /**
     * Construct query.
     *
     * @param Table $table The table this query is starting on
     */
    public function __construct(Table $table)
    {
        parent::__construct($table);

        $this->_paginator = Paginator::create($this);
    }

    /**
     * Create query based on the existing query. This factory copies the internal
     * state of the given query to a new instance.
     */
    public static function fromQuery(SelectQuery $query)
    {
        $obj = new static($query->getRepository());

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
     * @param Cursor|int[]|string[] $cursor
     */
    public function cursor($cursor = [])
    {
        $this->_cursor = $cursor;

        return $this;
    }

    /**
     * Execute query and paginate them.
     */
    public function paginate(): PaginatedInterface
    {
        return $this->_paginator->paginate($this->_cursor);
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy(ExpressionInterface|\Closure|array|string $fields, bool $overwrite = false)
    {
        parent::orderBy($fields, $overwrite);
        $this->_executeOrder($this->clause('order'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderByAsc(ExpressionInterface|\Closure|string $field, bool $overwrite = false)
    {
        parent::orderByAsc($field, $overwrite);
        $this->_executeOrder($this->clause('order'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderByDesc(ExpressionInterface|\Closure|string $field, bool $overwrite = false)
    {
        parent::orderByDesc($field, $overwrite);
        $this->_executeOrder($this->clause('order'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit($num)
    {
        parent::limit($num);
        $this->_executeLimit($this->clause('limit'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): ResultSetInterface
    {
        $items = $this->paginate()->items();
        return new ResultSet(is_array($items) ? $items : iterator_to_array($items));
    }

    /**
     * {@inheritdoc}
     */
    protected function _performCount(): int
    {
        return $this->all()->count();
    }

    protected function _executeOrder(?OrderByExpression $order): void
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
                    throw new BadKeywordException('OrderClauseExpression does not have direction');
                }

                /** @var string $direction */
                $direction = $matches['direction'];

                /** @var ExpressionInterface|string $field */
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
     * @param null|int|QueryExpression $limit
     */
    protected function _executeLimit($limit): void
    {
        if (is_int($limit)) {
            $this->_paginator->limit($limit);
            return;
        }

        if ($limit instanceof QueryExpression) {
            $generator = $this->getValueBinder();
            $generator->resetCount();
            $sql = $limit->sql($generator);

            if (!ctype_digit($sql) || $sql <= 0) {
                throw new LimitParameterException('Limit must be positive integer');
            }

            $this->_paginator->limit((int)$sql);
            return;
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $method, array $arguments)
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
            $this->_paginator->$method(...$arguments);
            return $this;
        }

        throw new \BadMethodCallException('Method ' . __CLASS__ . '::' . $method . ' does not exist');
    }

    /**
     * {@inheritdoc}
     */
    public function __debugInfo(): array
    {
        try {
            $info = $this->_paginator->build($this->_cursor)->__debugInfo();
        } catch (InsufficientConstraintsException $e) {
            $info = [
                'sql' => 'SQL could not be generated for this query as it is incomplete: ' . $e->getMessage(),
                'params' => [],
                'defaultTypes' => $this->getDefaultTypes(),
                'decorators' => count($this->_resultDecorators),
                'executed' => (bool)$this->_statement,
                'hydrate' => $this->_hydrate,
                'formatters' => count($this->_formatters),
                'mapReducers' => count($this->_mapReduce),
                'contain' => $this->_eagerLoader ? $this->_eagerLoader->getContain() : [],
                'matching' => $this->_eagerLoader ? $this->_eagerLoader->getMatching() : [],
                'extraOptions' => $this->_options,
                'repository' => $this->_repository,
            ];
        }

        return [
            '(help)' => 'This is a Lampager Query object to get the paginated results.',
            'paginator' => $this->_paginator,
        ] + $info;
    }
}
