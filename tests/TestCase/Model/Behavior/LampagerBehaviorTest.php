<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\TestCase\Model\Behavior;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Generator;
use Lampager\Cake\Model\Behavior\LampagerBehavior;
use Lampager\Cake\ORM\Query;
use Lampager\Cake\Test\TestCase\TestCase;
use Lampager\PaginationResult;
use PHPUnit\Framework\Attributes\DataProvider;

class LampagerBehaviorTest extends TestCase
{
    public array $fixtures = [
        'plugin.Lampager\\Cake.Posts',
    ];

    #[DataProvider('valueProvider')]
    #[DataProvider('queryExpressionProvider')]
    public function testLampager(callable $factory, PaginationResult $expected): void
    {
        /** @var LampagerBehavior&Table $posts */
        $posts = TableRegistry::getTableLocator()->get('Posts');
        $posts->addBehavior(LampagerBehavior::class);

        /** @var Query $query */
        $query = $factory($posts);
        $this->assertJsonEquals($expected, $query->paginate());
    }

    public static function valueProvider(): Generator
    {
        yield 'Ascending forward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending backward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc('modified')
                    ->orderByAsc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending forward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id');
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward inclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending backward exclusive' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc('modified')
                    ->orderByDesc('id')
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];
    }

    public static function queryExpressionProvider(): Generator
    {
        yield 'Ascending forward start inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending forward exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward start exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Ascending backward inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Ascending backward exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByAsc($posts->selectQuery()->expr('modified'))
                    ->orderByAsc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward start exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => null,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending forward inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending forward exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->forward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => false,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward start exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'));
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => null,
                    'nextCursor' => null,
                ]
            ),
        ];

        yield 'Descending backward inclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 1,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Descending backward exclusive with QueryExpression' => [
            function (Table $posts) {
                /** @var LampagerBehavior&Table $posts */
                return $posts->lampager()
                    ->backward()
                    ->seekable()
                    ->exclusive()
                    ->limit(3)
                    ->orderByDesc($posts->selectQuery()->expr('modified'))
                    ->orderByDesc($posts->selectQuery()->expr('id'))
                    ->cursor([
                        'id' => 3,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]);
            },
            new PaginationResult(
                [
                    new Entity([
                        'id' => 4,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new DateTime('2017-01-01 11:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ]),
                ],
                [
                    'hasPrevious' => false,
                    'previousCursor' => null,
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 5,
                        'modified' => new DateTime('2017-01-01 10:00:00'),
                    ],
                ]
            ),
        ];
    }
}
