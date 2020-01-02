<?php

namespace Lampager\Cake\Test\TestCase;

use Lampager\Cake\ORM\Query;
use Lampager\Cake\Paginator;
use Lampager\Query\Order;
use PHPUnit\Framework\MockObject\MockObject;

class PaginatorTest extends TestCase
{
    public function testDebugInfo()
    {
        /** @var MockObject&Query */
        $builder = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paginator = (new Paginator($builder))
            ->orderBy('modified')
            ->orderBy('id')
            ->limit(3)
            ->forward()
            ->inclusive()
            ->seekable();

        $actual = $paginator->__debugInfo();
        $this->assertEquals([
            'query' => [
                'orders' => [
                    new Order('modified', 'asc'),
                    new Order('id', 'asc'),
                ],
                'limit' => 3,
                'forward' => true,
                'inclusive' => true,
                'seekable' => true,
            ],
        ], $actual);
    }
}
