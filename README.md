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

- PHP: ^5.6 || ^7.0
- CakePHP: ^3.6
- [lampager/lampager](https://github.com/lampager/lampager): ^0.4

## Installing

```bash
composer require lampager/lampager-cakephp
```

## Basic Usage

The `Plugin` is not provided. Simply install as a Composer package and use in
one of the following methods:

- Use in Controller (via `Lampager\Cake\Datasource\Paginator`)
- Use in Table (via `Lampager\Cake\Model\Behavior\LampagerBehavior`)

### Use in Controller

At first, load the default Paginator component with the Lampager `Paginator` in
your Controller class (`AppController` is preferable).

Accept cursor parameters from a request and pass it through
`PaginatorComponent::paginate()`. The `Paginator` also accepts `Query`
expression that is created by `Table` classes.

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

    public function index()
    {
        // Get cursor parameters
        $previous = json_decode($this->request->getQuery('previous_cursor'), true);
        $next = json_decode($this->request->getQuery('next_cursor'), true);
        $cursor = $previous ?: $next ?: [];

        /** @var \Lampager\PaginationResult<\Cake\ORM\Entity> $posts */
        $posts = $this->paginate('Posts', [
            // Lampager options
            // If the previous_cursor is not set, paginate forward; otherwise backward
            'forward' => !$previous,
            'cursor' => $cursor,
            'seekable' => true,

            // PaginatorComponent config
            'order' => [
                'created' => 'DESC',
                'id' => 'DESC',
            ],
            'limit' => 15,
        ]);
        $this->set('posts', $posts);
    }

    public function query()
    {
        // Get cursor parameters
        $previous = json_decode($this->request->getQuery('previous_cursor'), true);
        $next = json_decode($this->request->getQuery('next_cursor'), true);
        $cursor = $previous ?: $next ?: [];

        // Query expression can also be passed to PaginatorComponent::paginate() as normal
        $query = TableRegistry::getTableLocator()->get('Posts')
            ->where(['Posts.type' => 'public'])
            ->orderDesc('created')
            ->orderDesc('id')
            ->limit(15);

        /** @var \Lampager\PaginationResult<\Cake\ORM\Entity> $posts */
        $posts = $this->paginate($query, [
            // If the previous_cursor is not set, paginate forward; otherwise backward
            'forward' => !$previous,
            'cursor' => $cursor,
            'seekable' => true,
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

### Use in Table

Initialize `LampagerBehavior` in your Table class (`AppTable` is preferable)
and simply use `lampager()` from the `Table` class. The query builder extends
the plain old `\Cake\ORM\Query` and is mixed in with `\Lampager\Paginator`. Note
that some of the methods in `\Lampager\Paginator`, viz., `orderBy()`,
`orderByDesc()`, and `clearOrderBy()` are not exposed because their method
signatures are not compatible with the CakePHP query builder.

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

    /**
     * @return \Lampager\PaginationResult
     */
    public function latest(array $cursor = [])
    {
        return $this->lampager()
            ->forward()
            ->seekable()
            ->cursor($cursor)
            ->limit(10)
            ->orderDesc('Post.modified')
            ->orderDesc('Post.created')
            ->orderDesc('Post.id');
    }

    /**
     * @return \Lampager\PaginationResult
     */
    public function draft(array $cursor = [])
    {
        // The methods from the CakePHP query builder, e.g., where(), are available.
        // \Cake\Database\Expression\QueryExpression can be accepted as well.
        return $this->lampager()
            ->where(['type' => 'draft'])
            ->forward()
            ->seekable()
            ->cursor($cursor)
            ->limit(10)
            ->orderDesc($this->query()->newExpr('modified'))
            ->orderDesc($this->query()->newExpr('created'))
            ->orderDesc($this->query()->newExpr('id'));
    }
}
```
