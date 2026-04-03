<?php

return [
    'storage_disk' => env('JERSEY_STORAGE_DISK', env('FILESYSTEM_DISK', 'public')),

    'pricing' => [
        'extra_logo_fee' => (float) env('JERSEY_EXTRA_LOGO_FEE', 120),
        'custom_name_number_fee' => (float) env('JERSEY_CUSTOM_NAME_NUMBER_FEE', 180),
    ],

    'defaults' => [
        [
            'slug' => 'football-short-sleeve',
            'name' => 'Football Jersey (Short Sleeve)',
            'jersey_type' => 'football_short',
            'base_price' => 1800,
            'supports_sleeves' => true,
            'model_url' => '/models/jerseys/football-short.glb',
            'metadata' => [
                'views' => ['front', 'back', 'sleeve_left', 'sleeve_right'],
                'description' => 'Classic football match jersey.',
            ],
        ],
        [
            'slug' => 'football-long-sleeve',
            'name' => 'Long Sleeve Jersey',
            'jersey_type' => 'football_long',
            'base_price' => 2050,
            'supports_sleeves' => true,
            'model_url' => '/models/jerseys/football-long.glb',
            'metadata' => [
                'views' => ['front', 'back', 'sleeve_left', 'sleeve_right'],
                'description' => 'Long-sleeve performance jersey.',
            ],
        ],
        [
            'slug' => 'basketball-sleeveless',
            'name' => 'Basketball Jersey (Sleeveless)',
            'jersey_type' => 'basketball',
            'base_price' => 1700,
            'supports_sleeves' => false,
            'model_url' => '/models/jerseys/basketball.glb',
            'metadata' => [
                'views' => ['front', 'back'],
                'description' => 'Sleeveless basketball game jersey.',
            ],
        ],
        [
            'slug' => 'training-jersey',
            'name' => 'Training Jersey',
            'jersey_type' => 'training',
            'base_price' => 1500,
            'supports_sleeves' => true,
            'model_url' => '/models/jerseys/training.glb',
            'metadata' => [
                'views' => ['front', 'back', 'sleeve_left', 'sleeve_right'],
                'description' => 'Lightweight training top for academies.',
            ],
        ],
    ],
];
