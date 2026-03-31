<?php
return [
    'base_url' => getenv('BASE_URL') ?: 'http://localhost',
    'site_name' => 'Grupo Capital DF',
    'db_path' => __DIR__ . '/storage/blog.sqlite',
    'session_name' => 'gcdf_session',
    'upload_dir' => __DIR__ . '/uploads',
    'upload_url' => '/uploads',
    'admin_email' => 'admin@localhost',
    'security' => [
        'max_login_attempts' => 5,
        'lockout_minutes' => 15,
    ],
];
