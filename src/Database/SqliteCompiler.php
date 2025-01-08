<?php

declare(strict_types=1);

namespace Lampager\Cake\Database;

use Cake\Database\Query;
use Cake\Database\QueryCompiler;
use Cake\Database\ValueBinder;

class SqliteCompiler extends QueryCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function _buildSelectPart(array $parts, Query $query, ValueBinder $generator): string
    {
        if (!$query->clause('union')) {
            return parent::_buildSelectPart($parts, $query, $generator);
        }

        return sprintf('SELECT * FROM (%s', parent::_buildSelectPart($parts, $query, $generator));
    }

    /**
     * {@inheritdoc}
     */
    protected function _buildUnionPart(array $parts, Query $query, ValueBinder $generator): string
    {
        $parts = array_map(function ($p) use ($generator) {
            $p['query'] = $p['query']->sql($generator);
            $p['query'] = $p['query'][0] === '(' ? trim($p['query'], '()') : $p['query'];
            $prefix = $p['all'] ? 'ALL ' : '';
            return sprintf('%sSELECT * FROM (%s)', $prefix, $p['query']);
        }, $parts);

        return sprintf(")\nUNION %s", implode("\nUNION ", $parts));
    }
}
