<?php

declare(strict_types=1);

namespace Lampager\Cake\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Lampager\Cake\ORM\Query;

class LampagerBehavior extends Behavior
{
    public function lampager(): Query
    {
        $query = new Query($this->table()->getConnection(), $this->table());
        $query->select();

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function table(): Table
    {
        if (is_callable('parent::' . __FUNCTION__)) {
            return parent::table();
        }

        return $this->getTable();
    }
}
