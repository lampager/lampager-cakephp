<?php

namespace Lampager\Cake\Test\TestCase\Database;

use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Lampager\Cake\Test\TestCase\TestCase;

class SqliteCompilerTest extends TestCase
{
    public $fixtures = [
        'plugin.Lampager\\Cake.Posts',
    ];

    public function setUp()
    {
        $config = ConnectionManager::getConfig('test');
        $this->skipIf(strpos($config['driver'], 'Sqlite') === false, 'Not using Sqlite');
    }

    public function testSelect()
    {
        $posts = TableRegistry::getTableLocator()->get('Posts');

        $expected = '
            SELECT
                Posts.* AS "Posts__*"
            FROM
                posts Posts
            ORDER BY
                modified ASC
        ';

        $actual = $posts->find()
            ->select(['*'])
            ->orderAsc('modified')
            ->sql();

        $this->assertSqlEquals($expected, $actual);
    }

    public function testUnion()
    {
        $posts = TableRegistry::getTableLocator()->get('Posts');

        $expected = '
            SELECT
                *
            FROM
                (
                    SELECT
                        Posts.* AS "Posts__*"
                    FROM
                        posts Posts
                    ORDER BY
                        modified ASC
                )
            UNION ALL
            SELECT
                *
            FROM
                (
                    DATETIME(\'now\')
                )
        ';

        $actual = $posts->find()
            ->select(['*'])
            ->orderAsc('modified')
            ->unionAll($posts->query()->func()->now())
            ->sql();

        $this->assertSqlEquals($expected, $actual);
    }
}
