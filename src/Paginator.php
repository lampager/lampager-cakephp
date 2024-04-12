<?php

declare(strict_types=1);

namespace Lampager\Cake;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Generator;
use Lampager\Concerns\HasProcessor;
use Lampager\Contracts\Cursor;
use Lampager\Exceptions\Query\InsufficientConstraintsException;
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
     * @return static
     */
    public static function create(Query $builder)
    {
        return new static($builder);
    }

    /**
     * Construct paginator.
     */
    public function __construct(Query $builder)
    {
        $this->builder = $builder;
        $this->processor = new ArrayProcessor();
    }

    /**
     * Build CakePHP Query instance from Lampager Query config.
     */
    public function transform(LampagerQuery $query): Query
    {
        return $this->compileSelectOrUnionAll($query->selectOrUnionAll());
    }

    /**
     * Configure -> Transform.
     *
     * @param Cursor|int[]|string[] $cursor
     */
    public function build($cursor = []): Query
    {
        return $this->transform($this->configure($cursor));
    }

    /**
     * Execute query and paginate them.
     *
     * @param  Cursor|int[]|string[]  $cursor
     * @return mixed|PaginationResult
     */
    public function paginate($cursor = [])
    {
        $query = $this->configure($cursor);
        return $this->process($query, $this->transform($query)->toArray());
    }

    protected function compileSelectOrUnionAll(SelectOrUnionAll $selectOrUnionAll): Query
    {
        if ($selectOrUnionAll instanceof Select) {
            return $this->compileSelect($selectOrUnionAll);
        }

        if ($selectOrUnionAll instanceof UnionAll) {
            $supportQuery = $this->compileSelect($selectOrUnionAll->supportQuery());
            $mainQuery = $this->compileSelect($selectOrUnionAll->mainQuery());
            return $supportQuery->unionAll($mainQuery);
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    protected function compileSelect(Select $select): Query
    {
        if ($this->builder->clause('group') || $this->builder->clause('union')) {
            throw new InsufficientConstraintsException('group()/union() are not supported');
        }

        /** @var Table $repository */
        $repository = $this->builder->getRepository();

        /** @var Query $builder */
        $builder = $repository->selectQuery()
            ->where($this->builder->clause('where'))
            ->modifier($this->builder->clause('modifier'))
            ->join($this->builder->clause('join'))
            ->epilog($this->builder->clause('epilog'))
            ->setEagerLoader($this->builder->getEagerLoader());

        $this
            ->compileWhere($builder, $select)
            ->compileOrderBy($builder, $select)
            ->compileLimit($builder, $select);

        return $builder;
    }

    /**
     * @return $this
     */
    protected function compileWhere(Query $builder, Select $select)
    {
        $conditions = [];
        foreach ($select->where() as $group) {
            $conditions['OR'][] = iterator_to_array($this->compileWhereGroup($group));
        }
        $builder->where($conditions);
        return $this;
    }

    /**
     * @return \Generator<string,string>
     */
    protected function compileWhereGroup(ConditionGroup $group): Generator
    {
        /** @var Condition $condition */
        foreach ($group as $condition) {
            $column = $condition->left() . ' ' . $condition->comparator();
            $value = $condition->right();
            yield $column => $value;
        }
    }

    /**
     * @return $this
     */
    protected function compileOrderBy(Query $builder, Select $select)
    {
        $alias = $builder->getRepository()->getAlias();

        foreach ($select->orders() as $i => $order) {
            $column = $order->column();
            if (strpos($column, '.') === false) {
                $column = $alias . '.' . $column;
            }
            $builder->order([$column => $order->order()], $i === 0);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function compileLimit(Query $builder, Select $select)
    {
        $builder->limit($select->limit()->toInteger());
        return $this;
    }

    /**
     * Returns an array that can be used to describe the internal state of this object.
     */
    public function __debugInfo(): array
    {
        $query = $this->configure();

        return [
            'query' => [
                'orders' => $query->orders(),
                'limit' => $query->limit(),
                'forward' => $query->forward(),
                'inclusive' => $query->inclusive(),
                'seekable' => $query->seekable(),
            ],
        ];
    }
}
