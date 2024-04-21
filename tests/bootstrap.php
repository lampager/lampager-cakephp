<?php

declare(strict_types=1);

use function Cake\Core\env;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;
use Lampager\Cake\Database\Driver\Sqlite;

require_once __DIR__ . '/../vendor/autoload.php';

define('ROOT', dirname(__DIR__));

Configure::write('App.encoding', 'utf-8');

ConnectionManager::setConfig('test', [
    'url' => env('DB_DSN') ?: 'sqlite:///:memory:?className=' . Connection::class . '&driver=' . Sqlite::class . '&quoteIdentifiers=true',
]);

// Create test database schema
if (env('FIXTURE_SCHEMA_METADATA')) {
    $loader = new SchemaLoader();
    $loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
