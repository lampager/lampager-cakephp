<?php

declare(strict_types=1);

namespace Lampager\Cake\Database\Driver;

use Cake\Database\Driver\Sqlite as BaseSqlite;
use Cake\Database\QueryCompiler;
use Lampager\Cake\Database\SqliteCompiler;

class Sqlite extends BaseSqlite
{
    /**
     * {@inheritdoc}
     */
    public function newCompiler(): QueryCompiler
    {
        return new SqliteCompiler();
    }
}
