<?php

return [
    'connections' => [
        'database' => [
            'driver'      => 'database',
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 28830,
            // 'expire'   => 310,
        ],
    ]
];