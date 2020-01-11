<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\TestCase\Model;

use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Generator;
use Lampager\Cake\ArrayProcessor;
use Lampager\Cake\ORM\Query;
use Lampager\Cake\PaginationResult;
use Lampager\Cake\Paginator;
use Lampager\Cake\Test\TestCase\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ArrayProcessorTest extends TestCase
{
    /**
     * @param mixed[]  $options
     * @param ?mixed[] $cursor
     * @param Entity[] $rows
     * @dataProvider processProvider
     */
    public function testProcess(array $options, ?array $cursor, array $rows, PaginationResult $expected): void
    {
        /** @var MockObject&Table $repository */
        $repository = $this->createMock(Table::class);
        $repository->method('getAlias')->willReturn('Posts');

        /** @var MockObject&Query $builder */
        $builder = $this->createMock(Query::class);
        $builder->method('getRepository')->willReturn($repository);

        $paginator = new Paginator($builder);
        $paginator->fromArray($options);
        $query = $paginator->configure($cursor);

        $processor = new ArrayProcessor();
        $actual = $processor->process($query, $rows);
        $this->assertEquals($expected, $actual);
    }

    public function processProvider(): Generator
    {
        yield 'Option has prefix but entity does not have prefix' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'orders' => [
                    ['Posts.modified', 'ASC'],
                    ['Posts.id', 'ASC'],
                ],
            ],
            [
                'Posts.id' => 3,
                'Posts.modified' => new Time('2017-01-01 10:00:00'),
            ],
            [
                new Entity([
                    'id' => 1,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 3,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 5,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 2,
                    'modified' => new Time('2017-01-01 11:00:00'),
                ]),
                new Entity([
                    'id' => 4,
                    'modified' => new Time('2017-01-01 11:00:00'),
                ]),
            ],
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new Time('2017-01-01 11:00:00'),
                    ]),
                ], [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'Posts.id' => 1,
                        'Posts.modified' => new Time('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'Posts.id' => 4,
                        'Posts.modified' => new Time('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Option and entity both do not have prefix' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'orders' => [
                    ['modified', 'ASC'],
                    ['id', 'ASC'],
                ],
            ],
            [
                'id' => 3,
                'modified' => new Time('2017-01-01 10:00:00'),
            ],
            [
                new Entity([
                    'id' => 1,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 3,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 5,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 2,
                    'modified' => new Time('2017-01-01 11:00:00'),
                ]),
                new Entity([
                    'id' => 4,
                    'modified' => new Time('2017-01-01 11:00:00'),
                ]),
            ],
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new Time('2017-01-01 11:00:00'),
                    ]),
                ], [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 4,
                        'modified' => new Time('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];

        yield 'Option with prefix and without prefix exist and entity does not have prefix' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'orders' => [
                    ['Posts.modified', 'ASC'],
                    ['id', 'ASC'],
                ],
            ],
            [
                'id' => 3,
                'Posts.modified' => new Time('2017-01-01 10:00:00'),
            ],
            [
                new Entity([
                    'id' => 1,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 3,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 5,
                    'modified' => new Time('2017-01-01 10:00:00'),
                ]),
                new Entity([
                    'id' => 2,
                    'modified' => new Time('2017-01-01 11:00:00'),
                ]),
                new Entity([
                    'id' => 4,
                    'modified' => new Time('2017-01-01 11:00:00'),
                ]),
            ],
            new PaginationResult(
                [
                    new Entity([
                        'id' => 3,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 5,
                        'modified' => new Time('2017-01-01 10:00:00'),
                    ]),
                    new Entity([
                        'id' => 2,
                        'modified' => new Time('2017-01-01 11:00:00'),
                    ]),
                ], [
                    'hasPrevious' => true,
                    'previousCursor' => [
                        'id' => 1,
                        'Posts.modified' => new Time('2017-01-01 10:00:00'),
                    ],
                    'hasNext' => true,
                    'nextCursor' => [
                        'id' => 4,
                        'Posts.modified' => new Time('2017-01-01 11:00:00'),
                    ],
                ]
            ),
        ];
    }
}
