<?php

namespace Lampager\Cake\Model\Behavior;

use Cake\ORM\Behavior;
use Lampager\Cake\ORM\Query;

class LampagerBehavior extends Behavior
{
    /**
     * @return Query
     */
    public function lampager()
    {
        $query = new Query($this->getTable()->getConnection(), $this->getTable());
        $query->select();

        return $this->getTable()->callFinder('all', $query, []);
    }
}
