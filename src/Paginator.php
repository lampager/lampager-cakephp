<?php

namespace Lampager\Cake;

use Lampager\Cake\ORM\Query;
use Lampager\Concerns\HasProcessor;
use Lampager\Contracts\Cursor;
use Lampager\PaginationResult;
use Lampager\Paginator as BasePaginator;
use Lampager\Query as LampagerQuery;
use Lampager\Query\Condition;
use Lampager\Query\ConditionGroup;
use Lampager\Query\Select;
use Lampager\Query\SelectOrUnionAll;
use Lampager\Query\UnionAll;

class Paginator extends BasePaginator
{
    use HasProcessor;

    /** @var Query $builder */
    public $builder;

    /**
     * Create paginator.
     *
     * @param Query $builder
     * @return static
     */
    public static function create(Query $builder)
    {
        return new static($builder);
    }

    /**
     * Construct paginator.
     *
     * @param Query $builder
     */
    public function __construct(Query $builder)
    {
        $this->builder = $builder;
        $this->processor = new ArrayProcessor();
    }

    /**
     * Build CakePHP Query instance from Lampager Query config.
     *
     * @param  LampagerQuery $query
     * @return Query
     */
    public function transform(LampagerQuery $query)
    {
        return $this->compileSelectOrUnionAll($query->selectOrUnionAll());
    }

    /**
     * Configure -> Transform.
     *
     * @param  Cursor|int[]|string[] $cursor
     * @return Query
     */
    public function build($cursor = [])
    {
        return $this->transform($this->configure($cursor));
    }

    /**
     * Execute query and paginate them.
     *
     * @param  Cursor|int[]|string[]  $cursor
     * @return mixed|PaginationResult
     */
    public function paginate($cursor)
    {
        $query = $this->configure($cursor);
        return $this->process($query, $this->transform($query)->toArray());
    }

    /**
     * @param  SelectOrUnionAll $selectOrUnionAll
     * @return Query
     */
    protected function compileSelectOrUnionAll(SelectOrUnionAll $selectOrUnionAll)
    {
        $repository = $this->builder->getRepository();

        if ($selectOrUnionAll instanceof Select) {
            return $this->compileSelect($repository->query(), $selectOrUnionAll);
        }

        if ($selectOrUnionAll instanceof UnionAll) {
            $supportQuery = $this->compileSelect($repository->query(), $selectOrUnionAll->supportQuery());
            $mainQuery = $this->compileSelect($repository->query(), $selectOrUnionAll->mainQuery());
            return $supportQuery->unionAll($mainQuery);
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param  Query  $builder
     * @param  Select $select
     * @return Query
     */
    protected function compileSelect($builder, Select $select)
    {
        $this
            ->compileWhere($builder, $select)
            ->compileOrderBy($builder, $select)
            ->compileLimit($builder, $select);
        return $builder;
    }

    /**
     * @param  Query  $builder
     * @param  Select $select
     * @return $this
     */
    protected function compileWhere($builder, Select $select)
    {
        $conditions = [];
        foreach ($select->where() as $group) {
            $conditions['OR'][] = iterator_to_array($this->compileWhereGroup($group));
        }
        $builder->where($conditions);
        return $this;
    }

    /**
     * @param  ConditionGroup     $group
     * @return \Generator<string,string>
     */
    protected function compileWhereGroup(ConditionGroup $group)
    {
        /** @var Condition $condition */
        foreach ($group as $condition) {
            $column = $condition->left() . ' ' . $condition->comparator();
            $value = $condition->right();
            yield $column => $value;
        }
    }

    /**
     * @param  Query  $builder
     * @param  Select $select
     * @return $this
     */
    protected function compileOrderBy($builder, Select $select)
    {
        foreach ($select->orders() as $i => $order) {
            $builder->order([$order->column() => $order->order()], $i === 0);
        }
        return $this;
    }

    /**
     * @param  Query  $builder
     * @param  Select $select
     * @return $this
     */
    protected function compileLimit($builder, Select $select)
    {
        $builder->limit($select->limit()->toInteger());
        return $this;
    }

    /**
     * Returns an array that can be used to describe the internal state of this object.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'query' => isset($this->query) ? [
                'orders' => $this->query->orders(),
                'limit' => $this->query->limit(),
                'forward' => $this->query->forward(),
                'inclusive' => $this->query->inclusive(),
                'seekable' => $this->query->seekable(),
                'direction' => $this->query->direction(),
                'cursor' => $this->query->cursor(),
            ] : null,
        ];
    }
}
