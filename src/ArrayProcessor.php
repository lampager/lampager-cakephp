<?php

namespace Lampager\Cake;

use Cake\ORM\Entity;
use Lampager\ArrayProcessor as BaseArrayProcessor;
use Lampager\Cake\ORM\Query;
use Lampager\Query as LampagerQuery;

class ArrayProcessor extends BaseArrayProcessor
{
    /**
     * Remove alias if prexied.
     * e.g., Foos.bar -> bar
     *
     * @param  string $column Current column
     * @param  string $alias  Current model alias
     * @return string         Unaliased column where applicable
     */
    protected function removeAlias($column, $alias)
    {
        if (strpos($column, "{$alias}.") !== 0) {
            return $column;
        }

        return substr($column, strlen("{$alias}."));
    }

    /**
     * {@inheritDoc}
     */
    protected function field($row, $column)
    {
        if (!isset($row[$column]) && $row instanceof Entity) {
            $unaliased_column = $this->removeAlias($column, $row->getSource());

            if (isset($row[$unaliased_column])) {
                return $row[$unaliased_column];
            }
        }

        return parent::field($row, $column);
    }

    /**
     * {@inheritDoc}
     */
    protected function makeCursor(LampagerQuery $query, $row)
    {
        /** @var Query $builder */
        $builder = $query->builder();
        $alias = $builder->getRepository()->getAlias();

        /** @var string[] $cursor */
        $cursor = [];

        foreach ($query->orders() as $order) {
            if (isset($row[$order->column()])) {
                $cursor[$order->column()] = $row[$order->column()];
                continue;
            }

            $column = $this->removeAlias($order->column(), $alias);

            if (isset($row[$column])) {
                $cursor[$order->column()] = $row[$column];
                continue;
            }
        }

        return $cursor;
    }
}
