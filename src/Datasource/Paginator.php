<?php

declare(strict_types=1);

namespace Lampager\Cake\Datasource;

use Cake\Datasource\Paging\NumericPaginator as CakePaginator;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Datasource\QueryInterface;
use Exception;
use Lampager\Cake\ORM\Query;
use Lampager\Cake\PaginationResult;
use Lampager\Exceptions\InvalidArgumentException;
use function Cake\Core\triggerWarning;

class Paginator extends CakePaginator
{
    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException if the \Lampager\Cake\ORM\Query is given
     * @return PaginationResult
     */
    public function paginate(mixed $target, array $params = [], array $settings = []): PaginatedInterface
    {
        $query = null;
        if ($target instanceof QueryInterface) {
            $query = $target;
            $target = $query->getRepository();
            if ($target === null) {
                throw new Exception('No repository set for query.');
            }
        }

        if ($query instanceof Query) {
            throw new InvalidArgumentException(Query::class . ' cannot be paginated by ' . __METHOD__ . '()');
        }

        $alias = $target->getAlias();
        $defaults = $this->getDefaults($alias, $settings);

        $validSettings = [
            ...array_keys($this->_defaultConfig),
            'order',
            'forward',
            'backward',
            'exclusive',
            'inclusive',
            'seekable',
            'unseekable',
            'cursor',
        ];
        $extraSettings = array_diff_key($defaults, array_flip($validSettings));
        if ($extraSettings) {
            triggerWarning(
                'Passing query options as paginator settings is no longer supported.'
                . ' Use a custom finder through the `finder` config or pass a Query instance to paginate().'
                . ' Extra keys found are: `' . implode('`, `', array_keys($extraSettings)) . '`.'
            );
        }

        $options = $this->mergeOptions($params, $defaults);
        $options = $this->validateSort($target, $options);
        $options = $this->checkLimit($options);

        $options += ['cursor' => [], 'scope' => null];

        if ($query === null) {
            $args = [];
            $type = $options['finder'] ?? 'all';
            if (is_array($type)) {
                $args = (array)current($type);
                $type = key($type);
            }
            $query = $target->find($type, ...$args);
        }

        $query = Query::fromQuery($query->applyOptions($options));
        $query->fromArray($options);
        $query->cursor($options['cursor']);

        return $query->paginate();
    }
}
