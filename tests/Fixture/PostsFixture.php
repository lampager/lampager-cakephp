<?php

declare(strict_types=1);

namespace Lampager\Cake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PostsFixture extends TestFixture
{
    public $records = [
        ['id' => 1, 'modified' => '2017-01-01 10:00:00'],
        ['id' => 3, 'modified' => '2017-01-01 10:00:00'],
        ['id' => 5, 'modified' => '2017-01-01 10:00:00'],
        ['id' => 2, 'modified' => '2017-01-01 11:00:00'],
        ['id' => 4, 'modified' => '2017-01-01 11:00:00'],
    ];
}
