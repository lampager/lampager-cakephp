<?php

namespace Lampager\Cake\Datasource;

use Cake\Datasource\Paginator as CakePaginator;
use Cake\Datasource\QueryInterface;
use Lampager\Cake\ORM\Query;
use Lampager\Cake\PaginationResult;

class Paginator extends CakePaginator
{
    /**
     * {@inheritdoc}
     * @return PaginationResult
     */
    public function paginate($object, array $params = [], array $settings = [])
    {
        $query = null;
        if ($object instanceof QueryInterface) {
            $query = $object;
            $object = $query->getRepository();
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
