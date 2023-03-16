<?php

declare(strict_types=1);

use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;
use Lampager\Cake\Database\Driver\Sqlite;

require_once __DIR__ . '/../vendor/cakephp/cakephp/src/basics.php';
require_once __DIR__ . '/../vendor/autoload.php';

ConnectionManager::setConfig('test', [
    'url' => env('DB_DSN') ?: 'sqlite:///:memory:?className=' . Connection::class . '&driver=' . Sqlite::class . '&quoteIdentifiers=true',
]);

// Create test database schema
if (env('FIXTURE_SCHEMA_METADATA')) {
    $loader = new SchemaLoader();
    $loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
