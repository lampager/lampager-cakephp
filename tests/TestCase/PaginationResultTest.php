<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\TestCase;

use ArrayIterator;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Generator;
use IteratorAggregate;
use Lampager\Cake\PaginationResult;
use Traversable;

class PaginationResultTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        set_error_handler(
            static function ($errno, $errstr, $errfile, $errline) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            },
            E_ALL
        );
    }

    public function tearDown(): void
    {
        restore_error_handler();

        parent::tearDown();
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testCurrentPage(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertEquals(0, $actual->currentPage());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testPerPage(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertEquals(3, $actual->perPage());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testTotalCount(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertNull($actual->totalCount());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testPageCount(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertNull($actual->pageCount());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testHasPrevPage(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertEquals((bool)$meta['hasPrevious'], $actual->hasPrevPage());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testHasNextPage(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertEquals((bool)$meta['hasNext'], $actual->hasNextPage());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testItems(array $entities, $records, array $meta): void
    {
        $paginationResult = new PaginationResult($records, $meta);
        $expected = is_array($records) ? $records : iterator_to_array($records);
        $actual = is_array($paginationResult->items()) ? $paginationResult->items() : iterator_to_array($paginationResult->items());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testPagingParam(array $entities, $records, array $meta): void
    {
        $actual = new PaginationResult($records, $meta);
        $this->assertEquals(count($entities), $actual->pagingParam('count'));
        $this->assertNull($actual->pagingParam('totalCount'));
        $this->assertEquals($meta['limit'], $actual->pagingParam('perPage'));
        $this->assertNull($actual->pagingParam('pageCount'));
        $this->assertEquals(0, $actual->pagingParam('currentPage'));
        $this->assertEquals($meta['hasPrevious'], $actual->pagingParam('hasPrevPage'));
        $this->assertEquals($meta['hasNext'], $actual->pagingParam('hasNextPage'));
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testJsonSerialize(array $entities, $records, array $meta, string $expected): void
    {
        $actual = json_encode(new PaginationResult($records, $meta));
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testDebugInfo(array $entities, $records, array $meta): void
    {
        $actual = (new PaginationResult($records, $meta))->__debugInfo();

        $this->assertEquals([
            '(help)' => 'This is a Lampager Pagination Result object.',
            'records' => $entities,
            'hasPrevious' => $meta['hasPrevious'],
            'previousCursor' => $meta['previousCursor'],
            'hasNext' => $meta['hasNext'],
            'nextCursor' => $meta['nextCursor'],
        ], $actual);
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testPublicProperties(array $entities, $records, array $meta): void
    {
        $paginationResult = new PaginationResult($records, $meta);

        $this->assertEquals($meta['hasPrevious'], $paginationResult->hasPrevious);
        $this->assertEquals($meta['previousCursor'], $paginationResult->previousCursor);
        $this->assertEquals($meta['hasNext'], $paginationResult->hasNext);
        $this->assertEquals($meta['nextCursor'], $paginationResult->nextCursor);
    }

    public static function arrayProvider(): Generator
    {
        yield 'Array iteration' => [
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
                    'Posts.id' => 2,
                    'Posts.modified' => new DateTime('2017-01-01 11:00:00'),
                ],
                'limit' => 3,
            ],
            '{
                "records": [
                    {
                        "id": 1,
                        "modified": "2017-01-01T10:00:00+00:00"
                    },
                    {
                        "id": 3,
                        "modified": "2017-01-01T10:00:00+00:00"
                    },
                    {
                        "id": 5,
                        "modified": "2017-01-01T10:00:00+00:00"
                    }
                ],
                "hasPrevious": null,
                "previousCursor": null,
                "hasNext": true,
                "nextCursor": {
                    "Posts.id": 2,
                    "Posts.modified": "2017-01-01T11:00:00+00:00"
                }
            }',
        ];

        yield 'ArrayIterator iteration' => [
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
            new ArrayIterator([
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
            ]),
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Posts.id' => 2,
                    'Posts.modified' => new DateTime('2017-01-01 11:00:00'),
                ],
                'limit' => 3,
            ],
            '{
                "records": [
                    {
                        "id": 1,
                        "modified": "2017-01-01T10:00:00+00:00"
                    },
                    {
                        "id": 3,
                        "modified": "2017-01-01T10:00:00+00:00"
                    },
                    {
                        "id": 5,
                        "modified": "2017-01-01T10:00:00+00:00"
                    }
                ],
                "hasPrevious": null,
                "previousCursor": null,
                "hasNext": true,
                "nextCursor": {
                    "Posts.id": 2,
                    "Posts.modified": "2017-01-01T11:00:00+00:00"
                }
            }',
        ];
    }

    public static function iteratorAggregateProvider(): Generator
    {
        yield 'IteratorAggregate iteration' => [
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
            new class implements IteratorAggregate {
                public function getIterator(): Traversable
                {
                    return new ArrayIterator([
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
                    ]);
                }
            },
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Posts.id' => 2,
                    'Posts.modified' => new DateTime('2017-01-01 11:00:00'),
                ],
                'limit' => 3,
            ],
            '{
                "records": [
                    {
                        "id": 1,
                        "modified": "2017-01-01T10:00:00+00:00"
                    },
                    {
                        "id": 3,
                        "modified": "2017-01-01T10:00:00+00:00"
                    },
                    {
                        "id": 5,
                        "modified": "2017-01-01T10:00:00+00:00"
                    }
                ],
                "hasPrevious": null,
                "previousCursor": null,
                "hasNext": true,
                "nextCursor": {
                    "Posts.id": 2,
                    "Posts.modified": "2017-01-01T11:00:00+00:00"
                }
            }',
        ];
    }
}
