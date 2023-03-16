<?php
declare(strict_types=1);

return [
[
    'table' => 'posts',
        'columns' => [
            'id' => ['type' => 'integer'],
            'modified' => ['type' => 'datetime'],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
];
