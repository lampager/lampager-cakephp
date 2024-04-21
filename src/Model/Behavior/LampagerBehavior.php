<?php

declare(strict_types=1);

namespace Lampager\Cake\Model\Behavior;

use Cake\ORM\Behavior;
use Lampager\Cake\ORM\Query;

class LampagerBehavior extends Behavior
{
    public function lampager(): Query
    {
        return new Query($this->table());
    }
}
