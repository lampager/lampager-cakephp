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
}
