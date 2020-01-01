<?php

namespace Lampager\Cake\Test\TestCase;

use Cake\I18n\Time;
use Cake\ORM\Entity;
use Lampager\Cake\PaginationResult;

class PaginationResultTest extends TestCase
{
    /**
     * @dataProvider resultProvider
     */
    public function testIteratorCurrent(array $entities, array $meta)
    {
        $actual = new PaginationResult($entities, $meta);

        $this->assertSame($entities[0], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(1, $actual->key());
        $this->assertSame($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(2, $actual->key());
        $this->assertSame($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertSame(0, $actual->key());
        $this->assertSame($entities[0], $actual->current());
    }

    /**
     * @dataProvider resultProvider
     */
    public function testIteratorKey(array $entities, array $meta)
    {
        $actual = new PaginationResult($entities, $meta);

        $this->assertSame(0, $actual->key());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(1, $actual->key());
        $this->assertSame($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(2, $actual->key());
        $this->assertSame($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertSame(0, $actual->key());
        $this->assertSame($entities[0], $actual->current());
    }

    /**
     * @dataProvider resultProvider
     */
    public function testIteratorNext(array $entities, array $meta)
    {
        $actual = new PaginationResult($entities, $meta);

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(1, $actual->key());
        $this->assertSame($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(2, $actual->key());
        $this->assertSame($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertSame(0, $actual->key());
        $this->assertSame($entities[0], $actual->current());
    }

    /**
     * @dataProvider resultProvider
     */
    public function testIteratorValid(array $entities, array $meta)
    {
        $actual = new PaginationResult($entities, $meta);

        $this->assertTrue($actual->valid());
        $this->assertSame(0, $actual->key());
        $this->assertSame($entities[0], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(1, $actual->key());
        $this->assertSame($entities[1], $actual->current());

        $actual->next();
        $this->assertTrue($actual->valid());
        $this->assertSame(2, $actual->key());
        $this->assertSame($entities[2], $actual->current());

        $actual->next();
        $this->assertFalse($actual->valid());

        $actual->rewind();
        $this->assertTrue($actual->valid());
        $this->assertSame(0, $actual->key());
        $this->assertSame($entities[0], $actual->current());
    }

    /**
     * @dataProvider resultProvider
     */
    public function testJsonSerialize(array $entities, array $meta, $expected)
    {
        $actual = json_encode(new PaginationResult($entities, $meta));
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @dataProvider resultProvider
     */
    public function testSerializeAndUnserialize(array $entities, array $meta)
    {
        $actual = unserialize(serialize(new PaginationResult($entities, $meta)));
        $expected = new PaginationResult($entities, $meta);
        $this->assertJsonEquals($expected, $actual);
    }

    public function resultProvider()
    {
        yield [
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
    }
}
