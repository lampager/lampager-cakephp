<?php

namespace Lampager\Cake\Test\TestCase;

use ArrayIterator;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use IteratorAggregate;
use Lampager\Cake\PaginationResult;
use PHPUnit\Framework\MockObject\MockObject;
use Traversable;

class PaginationResultTest extends TestCase
{
    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testIteratorCurrent(array $entities, $records, array $meta)
    {
        $actual = new PaginationResult($records, $meta);

        $this->assertEquals($entities[0], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(1, $actual->key());
        $this->assertEquals($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(2, $actual->key());
        $this->assertEquals($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertEquals(0, $actual->key());
        $this->assertEquals($entities[0], $actual->current());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testIteratorKey(array $entities, $records, array $meta)
    {
        $actual = new PaginationResult($records, $meta);

        $this->assertEquals(0, $actual->key());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(1, $actual->key());
        $this->assertEquals($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(2, $actual->key());
        $this->assertEquals($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertEquals(0, $actual->key());
        $this->assertEquals($entities[0], $actual->current());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testIteratorNext(array $entities, $records, array $meta)
    {
        $actual = new PaginationResult($records, $meta);

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(1, $actual->key());
        $this->assertEquals($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(2, $actual->key());
        $this->assertEquals($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertEquals(0, $actual->key());
        $this->assertEquals($entities[0], $actual->current());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testIteratorValid(array $entities, $records, array $meta)
    {
        $actual = new PaginationResult($records, $meta);

        $this->assertTrue($actual->valid());
        $this->assertEquals(0, $actual->key());
        $this->assertEquals($entities[0], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(1, $actual->key());
        $this->assertEquals($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertEquals(2, $actual->key());
        $this->assertEquals($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertEquals(0, $actual->key());
        $this->assertEquals($entities[0], $actual->current());
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @param string                       $expected
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testJsonSerialize(array $entities, $records, array $meta, $expected)
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
    public function testSerializeAndUnserialize(array $entities, $records, array $meta)
    {
        $actual = unserialize(serialize(new PaginationResult($records, $meta)));
        $expected = new PaginationResult($records, $meta);
        $this->assertJsonEquals($expected, $actual);
    }

    /**
     * @param Entity[]                     $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[]                      $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testDebugInfo(array $entities, $records, array $meta)
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
     * @param Entity[] $entities
     * @param Entity[]|Traversable<Entity> $records
     * @param mixed[] $meta
     * @dataProvider arrayProvider
     * @dataProvider iteratorAggregateProvider
     */
    public function testPublicProperties(array $entities, $records, array $meta)
    {
        $paginationResult = new PaginationResult($records, $meta);

        $this->assertEquals($meta['hasPrevious'], $paginationResult->hasPrevious);
        $this->assertEquals($meta['previousCursor'], $paginationResult->previousCursor);
        $this->assertEquals($meta['hasNext'], $paginationResult->hasNext);
        $this->assertEquals($meta['nextCursor'], $paginationResult->nextCursor);
    }

    public function arrayProvider()
    {
        yield 'Array iteration' => [
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
            ],
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Posts.id' => 2,
                    'Posts.modified' => new Time('2017-01-01 11:00:00'),
                ],
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
            ],
            new ArrayIterator([
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
            ]),
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Posts.id' => 2,
                    'Posts.modified' => new Time('2017-01-01 11:00:00'),
                ],
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

    public function iteratorAggregateProvider()
    {
        /** @var IteratorAggregate&MockObject $iteratorAggregate */
        $iteratorAggregate = $this->getMockForAbstractClass(IteratorAggregate::class);
        $iteratorAggregate->method('getIterator')->willReturn(new ArrayIterator([
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
        ]));

        yield 'IteratorAggregate iteration' => [
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
            ],
            $iteratorAggregate,
            [
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Posts.id' => 2,
                    'Posts.modified' => new Time('2017-01-01 11:00:00'),
                ],
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
