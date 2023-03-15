<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\TestCase\ORM;

use Cake\Database\Expression\OrderClauseExpression;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Generator;
use Lampager\Cake\Model\Behavior\LampagerBehavior;
use Lampager\Cake\ORM\Query;
use Lampager\Cake\PaginationResult;
use Lampager\Cake\Paginator;
use Lampager\Cake\Test\TestCase\TestCase;
use Lampager\Contracts\Exceptions\LampagerException;
use Lampager\Exceptions\Query\BadKeywordException;
use Lampager\Exceptions\Query\InsufficientConstraintsException;
use Lampager\Exceptions\Query\LimitParameterException;
use PHPUnit\Framework\MockObject\MockObject;

class QueryTest extends TestCase
{
    public $fixtures = [
        'plugin.Lampager\\Cake.Posts',
    ];

    /**
     * @dataProvider orderProvider
     */
    public function testOrder(callable $factory, PaginationResult $expected): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        /** @var Query $query */
        $query = $factory($posts);
        $this->assertJsonEquals($expected, $query->all());
    }

    /**
     * @dataProvider orderProvider
     */
    public function testOrderClear(callable $factory): void
    {
        $this->expectException(LampagerException::class);
        $this->expectExceptionMessage('At least one order constraint required');

        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        /** @var Query $query */
        $query = $factory($posts);
        $query->order([], true);
        $query->all();
    }

    public function testOrderIllegal(): void
    {
        $this->expectException(BadKeywordException::class);
        $this->expectExceptionMessage('OrderClauseExpression does not have direction');

        /** @var MockObject&OrderClauseExpression $expression */
        $expression = $this->getMockBuilder(OrderClauseExpression::class)->disableOriginalConstructor()->getMock();
        $expression->method('sql')->willReturn('modified');

        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);
        $posts->lampager()
            ->order([$expression])
            ->all();
    }

    public function testOrderQueryExpression(): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $expected = new PaginationResult(
            [
                new Entity([
                    'id' => 1,
                    'modified' => new FrozenTime('2017-01-01 10:00:00'),
                ]),
            ],
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'id' => 3,
                    'modified' => new FrozenTime('2017-01-01 10:00:00'),
                ],
            ]
        );

        $actual = $posts->lampager()
            ->order([$posts->query()->newExpr(['modified'])])
            ->order([$posts->query()->newExpr(['id'])])
            ->limit(1)
            ->all();

        $this->assertJsonEquals($expected, $actual);
    }

    public function testLimitQueryExpression(): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $expected = new PaginationResult(
            [
                new Entity([
                    'id' => 1,
                    'modified' => new FrozenTime('2017-01-01 10:00:00'),
                ]),
            ],
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'id' => 3,
                    'modified' => new FrozenTime('2017-01-01 10:00:00'),
                ],
            ]
        );

        $actual = $posts->lampager()
            ->orderAsc('modified')
            ->orderAsc('id')
            ->limit($posts->query()->newExpr(['1']))
            ->all();

        $this->assertJsonEquals($expected, $actual);
    }

    public function testLimitIllegalQueryExpression(): void
    {
        $this->expectException(LimitParameterException::class);
        $this->expectExceptionMessage('Limit must be positive integer');

        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);
        $posts->lampager()
            ->orderAsc('modified')
            ->orderAsc('id')
            ->limit($posts->query()->newExpr(['1 + 1']))
            ->all();
    }

    public function testWhere(): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $expected = new PaginationResult(
            [
                new Entity([
                    'id' => 3,
                    'modified' => new FrozenTime('2017-01-01 10:00:00'),
                ]),
            ],
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'id' => 5,
                    'modified' => new FrozenTime('2017-01-01 10:00:00'),
                ],
            ]
        );

        $actual = $posts->lampager()
            ->where(['id >' => 1])
            ->orderAsc('modified')
            ->orderAsc('id')
            ->limit(1)
            ->all();

        $this->assertJsonEquals($expected, $actual);
    }

    public function testGroup(): void
    {
        $this->expectException(InsufficientConstraintsException::class);
        $this->expectExceptionMessage('group()/union() are not supported');

        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);
        $posts->lampager()
            ->orderAsc('modified')
            ->orderAsc('id')
            ->group('modified')
            ->all();
    }

    public function testUnion(): void
    {
        $this->expectException(InsufficientConstraintsException::class);
        $this->expectExceptionMessage('group()/union() are not supported');

        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);
        $posts->lampager()
            ->orderAsc('modified')
            ->orderAsc('id')
            ->union($posts->query()->select())
            ->all();
    }

    public function testCall(): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $actual = $posts->lampager()
            ->orderAsc('id')
            ->all()
            ->take();

        $expected = [
            new Entity([
                'id' => 1,
                'modified' => new FrozenTime('2017-01-01 10:00:00'),
            ]),
        ];

        $this->assertJsonEquals($expected, $actual);
    }

    public function testDebugInfo(): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $actual = $posts->lampager()
            ->orderAsc('modified')
            ->orderAsc('id')
            ->limit(3)
            ->__debugInfo();

        $this->assertSame('This is a Lampager Query object to get the paginated results.', $actual['(help)']);
        $this->assertInstanceOf(Paginator::class, $actual['paginator']);
        $this->assertTextStartsWith('SELECT ', $actual['sql']);
        $this->assertSame($posts, $actual['repository']);

        $this->assertArrayHasKey('params', $actual);
        $this->assertArrayHasKey('defaultTypes', $actual);
        $this->assertArrayHasKey('decorators', $actual);
        $this->assertArrayHasKey('executed', $actual);
        $this->assertArrayHasKey('hydrate', $actual);
        $this->assertArrayHasKey('buffered', $actual);
        $this->assertArrayHasKey('formatters', $actual);
        $this->assertArrayHasKey('mapReducers', $actual);
        $this->assertArrayHasKey('contain', $actual);
        $this->assertArrayHasKey('matching', $actual);
        $this->assertArrayHasKey('extraOptions', $actual);
    }

    public function testDebugInfoIncomplete(): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $actual = $posts->lampager()
            ->limit(3)
            ->__debugInfo();

        $this->assertSame('This is a Lampager Query object to get the paginated results.', $actual['(help)']);
        $this->assertInstanceOf(Paginator::class, $actual['paginator']);
        $this->assertSame('SQL could not be generated for this query as it is incomplete: At least one order constraint required', $actual['sql']);
        $this->assertSame($posts, $actual['repository']);

        $this->assertArrayHasKey('params', $actual);
        $this->assertArrayHasKey('defaultTypes', $actual);
        $this->assertArrayHasKey('decorators', $actual);
        $this->assertArrayHasKey('executed', $actual);
        $this->assertArrayHasKey('hydrate', $actual);
        $this->assertArrayHasKey('buffered', $actual);
        $this->assertArrayHasKey('formatters', $actual);
        $this->assertArrayHasKey('mapReducers', $actual);
        $this->assertArrayHasKey('contain', $actual);
        $this->assertArrayHasKey('matching', $actual);
        $this->assertArrayHasKey('extraOptions', $actual);
    }

    /**
     * @dataProvider countProvider
     */
    public function testCount(callable $factory, int $expected): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        $this->assertSame($expected, $factory($posts));
    }

    public function orderProvider(): Generator
    {
        yield 'Ascending and ascending' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->order([
                        'modified' => 'asc',
                        'id' => 'asc',
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending and descending' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->order([
                        'modified' => 'desc',
                        'id' => 'desc',
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];
    }

    public function countProvider(): Generator
    {
        yield 'Ascending forward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->count();
            },
            3,
        ];

        yield 'Ascending forward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->count();
            },
            3,
        ];

        yield 'Ascending forward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            3,
        ];

        yield 'Ascending forward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            3,
        ];

        yield 'Ascending backward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->count();
            },
            3,
        ];

        yield 'Ascending backward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->count();
            },
            3,
        ];

        yield 'Ascending backward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            2,
        ];

        yield 'Ascending backward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderAsc('modified')
                    ->orderAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            1,
        ];

        yield 'Descending forward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->count();
            },
            3,
        ];

        yield 'Descending forward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->count();
            },
            3,
        ];

        yield 'Descending forward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            2,
        ];

        yield 'Descending forward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            1,
        ];

        yield 'Descending backward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->count();
            },
            3,
        ];

        yield 'Descending backward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->count();
            },
            3,
        ];

        yield 'Descending backward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            3,
        ];

        yield 'Descending backward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderDesc('modified')
                    ->orderDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ])
                    ->count();
            },
            3,
        ];
    }
}
