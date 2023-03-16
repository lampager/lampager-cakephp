<?php

declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;
use Lampager\Cake\Database\Driver\Sqlite;

require_once __DIR__ . '/../vendor/cakephp/cakephp/src/basics.php';
require_once __DIR__ . '/../vendor/autoload.php';

define('ROOT', dirname(__DIR__));
define('CORE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS);
define('APP', ROOT . DS . 'tests' . DS . 'test_app' . DS);
define('CONFIG', APP);
define('TMP', sys_get_temp_dir() . DS);
define('CACHE', TMP . 'cache' . DS);

Configure::write('debug', true);
Configure::write('App', [
    'debug' => true,
    'namespace' => 'App',
    'encoding' => 'UTF-8',
]);

Cache::setConfig([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true,
        'path' => CACHE,
    ],
]);

if (!getenv('DB_DSN')) {
    putenv('DB_DSN=sqlite:///:memory:?className=' . Connection::class . '&driver=' . Sqlite::class . '&quoteIdentifiers=true');
}

ConnectionManager::setConfig('test', [
    'url' => getenv('DB_DSN'),
]);

// Create test database schema
if (env('FIXTURE_SCHEMA_METADATA')) {
    $loader = new SchemaLoader();
    $loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
