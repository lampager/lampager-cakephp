<?php

namespace Lampager\Cake\Database;

use Cake\Database\SqliteCompiler as BaseSqliteCompiler;

class SqliteCompiler extends BaseSqliteCompiler
{
    /**
     * {@inheritDoc}
     */
    protected function _buildSelectPart($parts, $query, $generator)
    {
        if (!$query->clause('union')) {
            return parent::_buildSelectPart($parts, $query, $generator);
        }

        return sprintf('SELECT * FROM (%s', parent::_buildSelectPart($parts, $query, $generator));
    }

    /**
     * {@inheritDoc}
     */
    protected function _buildUnionPart($parts, $query, $generator)
    {
        $parts = array_map(function ($p) use ($generator) {
            $p['query'] = $p['query']->sql($generator);
            $p['query'] = $p['query'][0] === '(' ? trim($p['query'], '()') : $p['query'];
            $prefix = $p['all'] ? 'ALL ' : '';
            return sprintf("%sSELECT * FROM(%s)", $prefix, $p['query']);
        }, $parts);

        return sprintf(")\nUNION %s", implode("\nUNION ", $parts));
    }
}
