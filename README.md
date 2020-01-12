<p align="center">
<img width="320" alt="lampager-cakephp" src="https://user-images.githubusercontent.com/1351893/31820647-42c45c7a-b5dd-11e7-9ac8-f1000e961662.png">
</p>
<p align="center">
<a href="https://travis-ci.com/lampager/lampager-cakephp"><img src="https://travis-ci.com/lampager/lampager-cakephp.svg?branch=master" alt="Build Status"></a>
<a href="https://coveralls.io/github/lampager/lampager-cakephp?branch=master"><img src="https://coveralls.io/repos/github/lampager/lampager-cakephp/badge.svg?branch=master" alt="Coverage Status"></a>
<a href="https://scrutinizer-ci.com/g/lampager/lampager-cakephp/?branch=master"><img src="https://scrutinizer-ci.com/g/lampager/lampager-cakephp/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
</p>

# Lampager for CakePHP

Rapid pagination without using OFFSET

## Requirements

- PHP: ^7.2
- CakePHP: ^4.0
- [lampager/lampager][]: ^0.4

### Note

- For CakePHP 2.x, use [lampager/lampager-cakephp2][].
- For CakePHP 3.x, use [lampager/lampager-cakephp (v1.x)][].
- For CakePHP 4.x, use lampager/lampager-cakephp v2.x (this version).

## Installing

```bash
composer require lampager/lampager-cakephp:^2.0
```

For SQLite users, see [SQLite](#sqlite) to configure.

## Basic Usage

Simply install as a Composer package and use in one or more of the following
methods:

- Use in Controller (via `\Lampager\Cake\Datasource\Paginator`)
- Use in Table (via `\Lampager\Cake\Model\Behavior\LampagerBehavior`)

### Use in Controller

At first, load the default Paginator component with the
`\Lampager\Cake\Datasource\Paginator` in your Controller class (`AppController`
is preferable).

```php
namespace App\Controller;

use Cake\Controller\Controller;
use Lampager\Cake\Datasource\Paginator;

class AppController extends Controller
{
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Paginator', [
            'paginator' => new Paginator(),
        ]);
    }
}
```

Use in a way described in the Cookbook: [Pagination][]. Note the options that
are specific to Lampager such as `forward`, `seekable`, or `cursor`.

```php
$query = $this->Posts
    ->where(['Posts.type' => 'public'])
    ->orderDesc('created')
    ->orderDesc('id')
    ->limit(10);

$posts = $this->paginate($query, [
    'forward' => true,
    'seekable' => true,
    'cursor' => [
        'id' => 4,
        'created' => '2020-01-01 10:00:00',
    ],
]);

$this->set('posts', $posts);
```

### Use in Table

Initialize `LampagerBehavior` in your Table class (`AppTable` is preferable)
and simply use `lampager()` there.

```php
namespace App\Model\Table;

use Cake\ORM\Table;
use Lampager\Cake\Model\Behavior\LampagerBehavior;

class AppTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior(LampagerBehavior::class);
    }
}
```

The query builder (`\Lampager\Cake\ORM\Query`) extends the plain old
`\Cake\ORM\Query` and is mixed in with `\Lampager\Paginator`. Note that some of
the methods in `\Lampager\Paginator`, viz., `orderBy()`, `orderByDesc()`, and
`clearOrderBy()` are not exposed because their method signatures are not
compatible with the CakePHP query builder.

```php
$cursor = [
    'id' => 4,
    'created' => '2020-01-01 10:00:00',
    'modified' => '2020-01-01 12:00:00',
];

/** @var \Lampager\Cake\PaginationResult $latest */
$latest = $this->lampager()
    ->forward()
    ->seekable()
    ->cursor($cursor)
    ->limit(10)
    ->orderDesc('Posts.modified')
    ->orderDesc('Posts.created')
    ->orderDesc('Posts.id');

foreach ($latest as $post) {
    /** @var \Cake\ORM\Entity $post */
    debug($post->id);
    debug($post->created);
    debug($post->modified);
}
```

The methods from the CakePHP query builder, e.g., `where()`, are available.
`\Cake\Database\Expression\QueryExpression` is accepted as well.

```php
/** @var \Lampager\Cake\PaginationResult $drafts */
$drafts = $this->lampager()
    ->where(['type' => 'draft'])
    ->forward()
    ->seekable()
    ->cursor($cursor)
    ->limit(10)
    ->orderDesc($this->query()->newExpr('modified'))
    ->orderDesc($this->query()->newExpr('created'))
    ->orderDesc($this->query()->newExpr('id'));

/** @var \Cake\ORM\Entity $sample */
$sample = $drafts->sample();

/** @var int $count */
$count = $drafts->count();
```

## Classes

See also: [lampager/lampager][].

| Name                                                | Type  | Parent Class<br>Implemented Interface  | Description                                                                         |
|:----------------------------------------------------|:------|:---------------------------------------|:------------------------------------------------------------------------------------|
| Lampager\\Cake\\ORM\\`Query`                        | Class | Cake\\ORM\\`Query`                     | Fluent factory implementation for CakePHP                                           |
| Lampager\\Cake\\Model\\Behavior\\`LampagerBehavior` | Class | Cake\\ORM\\`Behavior`                  | CakePHP behavior which returns Lampager\\Cake\\ORM\\`Query`                         |
| Lampager\\Cake\\Datasource\\`Paginator`             | Class | Cake\\Datasource\\`Paginator`          | CakePHP paginatior which delegates to Lampager\\Cake\\ORM\\`Query`                  |
| Lampager\\Cake\\`Paginator`                         | Class | Lampager\\`Paginator`                  | Paginator implementation for CakePHP                                                |
| Lampager\\Cake\\`ArrayProcessor`                    | Class | Lampager\\`ArrayProcessor`             | Processor implementation for CakePHP                                                |
| Lampager\\Cake\\`PaginationResult`                  | Class | Cake\\Datasource\\`ResultSetInterface` | PaginationResult implementation for CakePHP                                         |
| Lampager\\Cake\\Database\\`SqliteCompiler`          | Class | Cake\\Database\\`SqliteCompiler`       | Query compiler implementation for SQLite                                            |
| Lampager\\Cake\\Database\\Driver\\`Sqlite`          | Class | Cake\\Database\\Driver\\`Sqlite`       | Driver implementation which delegates to Lampager\\Cake\\Database\\`SqliteCompiler` |

Note that `\Lampager\Cake\PaginationResult` does not extend
`\Lampager\PaginationResult` as it conflicts with
`\Cake\Datasource\ResultSetInterface`.

## API

See also: [lampager/lampager][].

### LampagerBehavior::lampager()

Build a Lampager query from Table in exactly the same way as CakePHP.

```php
LampagerBehavior::lampager(): \Lampager\Cake\ORM\Query
```

### Paginator::\_\_construct()<br>Paginator::create()

Create a new paginator instance. These methods are not intended to be directly
used in your code.

```php
static Paginator::create(\Cake\ORM\Query $builder): static
Paginator::__construct(\Cake\ORM\Query $builder)
```

### Paginator::transform()

Transform a Lampager query into a CakePHP query.

```php
Paginator::transform(\Lampager\Query $query): \Cake\ORM\Query
```

### Paginator::build()

Perform configure + transform.

```php
Paginator::build(\Lampager\Contracts\Cursor|array $cursor = []): \Cake\ORM\Query
```

### Paginator::paginate()

Perform configure + transform + process.

```php
Paginator::paginate(\Lampager\Contracts\Cursor|array $cursor = []): \Lampager\Cake\PaginationResult
```

#### Arguments

- **`(mixed)`** __*$cursor*__<br> An associative array that contains `$column => $value` or an object that implements `\Lampager\Contracts\Cursor`. It must be **all-or-nothing**.
  - For the initial page, omit this parameter or pass an empty array.
  - For the subsequent pages, pass all the parameters. The partial one is not allowed.

#### Return Value

e.g.,

(Default format when using `\Cake\ORM\Query`)

```php
object(Lampager\Cake\PaginationResult)#1 (6) {
  ["(help)"]=>
  string(44) "This is a Lampager Pagination Result object."
  ["records"]=>
  array(3) {
    [0]=>
    object(Cake\ORM\Entity)#2 (11) { ... }
    [1]=>
    object(Cake\ORM\Entity)#3 (11) { ... }
    [2]=>
    object(Cake\ORM\Entity)#4 (11) { ... }
  ["hasPrevious"]=>
  bool(false)
  ["previousCursor"]=>
  NULL
  ["hasNext"]=>
  bool(true)
  ["nextCursor"]=>
  array(2) {
    ["created"]=>
    object(Cake\I18n\Time)#5 (3) {
      ["date"]=>
      string(26) "2017-01-01 10:00:00.000000"
      ["timezone_type"]=>
      int(3)
      ["timezone"]=>
      string(3) "UTC"
    }
    ["id"]=>
    int(1)
  }
}
```

### PaginationResult::\_\_call()

`\Lampager\Cake\PaginationResult` implements
`\Cake\Datasource\ResultSetInterface`. For how to make the best use of the
`PaginationResult`, please refer to the Cookbook: [Working with Result Sets][].

## Examples

This section describes the practical usage of lampager-cakephp.

### Use in Controller

The example below shows how to accept a cursor parameter from a request and pass
it through `PaginatorComponent::paginate()`. Be sure that your `AppController`
has properly initialized `Paginator` as above.

```php
namespace App\Controller;

class PostsController extends AppController
{
    /**
     * This method shows how to pass options by a query and array.
     */
    public function query()
    {
        // Get cursor parameters
        $previous = json_decode($this->request->getQuery('previous_cursor'), true);
        $next = json_decode($this->request->getQuery('next_cursor'), true);
        $cursor = $previous ?: $next ?: [];

        // Query expression can be passed to PaginatorComponent::paginate() as normal
        $query = $this->Posts
            ->where(['Posts.type' => 'public'])
            ->orderDesc('created')
            ->orderDesc('id')
            ->limit(15);

        /** @var \Lampager\Cake\PaginationResult<\Cake\ORM\Entity> $posts */
        $posts = $this->paginate($query, [
            // If the previous_cursor is not set, paginate forward; otherwise backward
            'forward' => !$previous,
            'cursor' => $cursor,
            'seekable' => true,
        ]);

        $this->set('posts', $posts);
    }

    /**
     * This method shows how to pass options from an array.
     */
    public function options()
    {
        // Get cursor parameters
        $previous = json_decode($this->request->getQuery('previous_cursor'), true);
        $next = json_decode($this->request->getQuery('next_cursor'), true);
        $cursor = $previous ?: $next ?: [];

        /** @var \Lampager\Cake\PaginationResult<\Cake\ORM\Entity> $posts */
        $posts = $this->paginate('Posts', [
            // Lampager options
            // If the previous_cursor is not set, paginate forward; otherwise backward
            'forward' => !$previous,
            'cursor' => $cursor,
            'seekable' => true,

            // PaginatorComponent config
            'conditions' => [
                'type' => 'public',
            ],
            'order' => [
                'created' => 'DESC',
                'id' => 'DESC',
            ],
            'limit' => 15,
        ]);

        $this->set('posts', $posts);
    }
}
```

And the pagination links can be output as follows:

```php
// If there is a next page, print pagination link
if ($posts->hasPrevious) {
    echo $this->Html->link('<< Previous', [
        'controller' => 'posts',
        'action' => 'index',
        '?' => [
            'previous_cursor' => json_encode($posts->previousCursor),
        ],
    ]);
}

// If there is a next page, print pagination link
if ($posts->hasNext) {
    echo $this->Html->link('Next >>', [
        'controller' => 'posts',
        'action' => 'index',
        '?' => [
            'next_cursor' => json_encode($posts->nextCursor),
        ],
    ]);
}
```

## Supported database engines

### MySQL, MariaDB, and PostgreSQL

Supported!

### Microsoft SQL Server

Not supported.

### SQLite

Supported but requires an additional configuration.

In SQLite `UNION ALL` statements cannot combine `SELECT` statements that have
`ORDER BY` clause. In order to get this to work, those `SELECT` statements have
to be wrapped by a subquery like `SELECT * FROM (...)`. CakePHP not natively
handling this situation, Lampager for CakePHP introduces
`\Lampager\Cake\Database\Driver\Sqlite` that needs to be installed on your
application. Configure like the following in your `config/app.php`:

```php
return [
    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => \Lampager\Cake\Database\Driver\Sqlite::class,
            'username' => '********',
            'password' => '********',
            'database' => '********',
        ],
    ],
];
```

[lampager/lampager]:                https://github.com/lampager/lampager
[lampager/lampager-cakephp (v1.x)]: https://github.com/lampager/lampager-cakephp/tree/v1.x
[lampager/lampager-cakephp2]:       https://github.com/lampager/lampager-cakephp2
[Pagination]:                       https://book.cakephp.org/3/en/controllers/components/pagination.html
[Working with Result Sets]:         https://book.cakephp.org/3/en/orm/retrieving-data-and-resultsets.html#working-with-result-sets
