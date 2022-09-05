<?php

return [
    'Development' => [
        'path' => 'dev',
        'setWritable' => [
            'runtime',
        ],
        'setExecutable' => [
            'yii',
            'yii_test',
        ],
    ],
    'Production' => [
        'path' => 'prod',
        'setWritable' => [
            'runtime',
        ],
        'setExecutable' => [
            'yii',
        ],

    ]
];
