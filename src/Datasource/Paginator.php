<?php

declare(strict_types=1);

namespace Lampager\Cake\Datasource;

use Cake\Datasource\Paging\NumericPaginator as CakePaginator;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Exception;
use Lampager\Cake\ORM\Query;
use Lampager\Cake\PaginationResult;
use Lampager\Exceptions\InvalidArgumentException;

class Paginator extends CakePaginator
{
    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException if the \Lampager\Cake\ORM\Query is given
     * @return PaginationResult
     */
    public function paginate(object $object, array $params = [], array $settings = []): ResultSetInterface
    {
        $query = null;
        if ($object instanceof QueryInterface) {
            $query = $object;
            $object = $query->getRepository();
            if ($object === null) {
                throw new Exception('No repository set for query.');
            }
        }

        if ($query instanceof Query) {
            throw new InvalidArgumentException(Query::class . ' cannot be paginated by ' . __METHOD__ . '()');
        }

        $alias = $object->getAlias();
        $defaults = $this->getDefaults($alias, $settings);
        $options = $this->mergeOptions($params, $defaults);
        $options = $this->validateSort($object, $options);
        $options = $this->checkLimit($options);

        $options += ['cursor' => [], 'scope' => null];

        list($finder, $options) = $this->_extractFinder($options);

        if (empty($query)) {
            $query = Query::fromQuery($object->find($finder, $options));
        } else {
            $query = Query::fromQuery($query->applyOptions($options));
        }

        $query->fromArray($options);
        $query->cursor($options['cursor']);

        return $query->all();
    }
}
