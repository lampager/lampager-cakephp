<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\TestCase\Datasource;

use Cake\Controller\Controller;
use Cake\Database\Expression\OrderClauseExpression;
use Cake\Datasource\QueryInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Exception;
use Generator;
use Lampager\Cake\Datasource\Paginator;
use Lampager\Cake\Model\Behavior\LampagerBehavior;
use Lampager\Cake\PaginationResult;
use Lampager\Cake\Test\TestCase\TestCase;
use Lampager\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

class PaginatorTest extends TestCase
{
    public $fixtures = [
        'plugin.Lampager\\Cake.Posts',
    ];

    /**
     * @dataProvider valueProvider
     * @dataProvider queryExpressionProvider
     */
    public function testPaginateTable(callable $factory, PaginationResult $expected): void
    {
        $controller = new Controller();
        $controller->paginate = [
            'className' => Paginator::class,
        ];

        $posts = $controller->fetchTable('Posts');

        /** @var mixed[] $options */
        $options = $factory($posts);

        $this->assertJsonEquals($expected, $controller->paginate('Posts', $options));
    }

    /**
     * @dataProvider valueProvider
     * @dataProvider queryExpressionProvider
     */
    public function testPaginateCakeQuery(callable $factory, PaginationResult $expected): void
    {
        $controller = new Controller();
        $controller->paginate = [
            'className' => Paginator::class,
        ];

        $posts = $controller->fetchTable('Posts');

        /** @var mixed[] $options */
        $options = $factory($posts);

        $this->assertJsonEquals($expected, $controller->paginate($posts->find('all'), $options));
    }

    /**
     * @dataProvider valueProvider
     * @dataProvider queryExpressionProvider
     */
    public function testPaginateLampagerCakeQuery(callable $factory): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lampager\Cake\ORM\Query cannot be paginated by Lampager\Cake\Datasource\Paginator::paginate()');

        $controller = new Controller();
        $controller->paginate = [
            'className' => Paginator::class,
        ];

        /** @var LampagerBehavior&Table $posts */
        $posts = $controller->fetchTable('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        /** @var mixed[] $options */
        $options = $factory($posts);
        $query = $posts->lampager()->applyOptions($options);
        $controller->paginate($query);
    }

    public function testPaginateInvalidQuery(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No repository set for query.');

        $controller = new Controller();
        $controller->paginate = [
            'className' => Paginator::class,
        ];

        /** @var MockObject&QueryInterface $query */
        $query = $this->getMockBuilder(QueryInterface::class)->getMock();
        $query->method('getRepository')->willReturn(null);

        $controller->paginate($query);
    }

    public function valueProvider(): Generator
    {
        yield 'Ascending forward start inclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                ];
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
                        'Posts.id' => 2,
                        'Posts.modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward start exclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                ];
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
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward inclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 1,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'Posts.id' => 4,
                        'Posts.modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward exclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start inclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start exclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward inclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
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
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending backward exclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'asc',
                        'id' => 'asc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'Posts.id' => 1,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start inclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                ];
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
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start exclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                ];
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
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward inclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending forward exclusive' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 1,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start inclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 2,
                        'Posts.modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start exclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward inclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 4,
                        'Posts.modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'Posts.id' => 1,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending backward exclusive' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        'modified' => 'desc',
                        'id' => 'desc',
                    ],
                    'cursor' => [
                        'Posts.id' => 3,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
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
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'Posts.id' => 5,
                        'Posts.modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];
    }

    public function queryExpressionProvider(): Generator
    {
        yield 'Ascending forward start inclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                ];
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

        yield 'Ascending forward start exclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                ];
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
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward inclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward exclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start inclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start exclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward inclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
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
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending backward exclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'asc'),
                        new OrderClauseExpression('id', 'asc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start inclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                ];
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

        yield 'Descending forward start exclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                ];
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
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward inclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending forward exclusive with QueryExpression' => [
            function () {
                return [
                    'forward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start inclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start exclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward inclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 2,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 4,
                        'modified' => new FrozenTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 1,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending backward exclusive with QueryExpression' => [
            function () {
                return [
                    'backward' => true,
                    'seekable' => true,
                    'exclusive' => true,
                    'limit' => 3,
                    'order' => [
                        new OrderClauseExpression('modified', 'desc'),
                        new OrderClauseExpression('id', 'desc'),
                    ],
                    'cursor' => [
                        'id' => 3,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ];
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
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new FrozenTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];
    }
}
