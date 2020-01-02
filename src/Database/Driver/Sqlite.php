<?php

namespace Lampager\Cake\Database\Driver;

use Cake\Database\Driver\Sqlite as BaseSqlite;
use Lampager\Cake\Database\SqliteCompiler;

class Sqlite extends BaseSqlite
{
    /**
     * {@inheritdoc}
     */
    public function newCompiler()
    {
        return new SqliteCompiler();
    }
}
