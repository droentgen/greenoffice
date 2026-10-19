<?php
/**
 * Copy this file to config/config.php and fill in your own values.
 * config/config.php is ignored by git so passwords never get committed.
 *
 * Database is PostgreSQL. For Supabase: Project Settings › Database › Connection string
 * (use the pooler host/port shown there for most hosting setups).
 */
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 5432,
        'name' => 'postgres',
        'user' => 'postgres',
        'pass' => '',
    ],

    // URL path of the public/ folder.
    // null  = detect automatically (works for XAMPP: http://localhost/green-office/public/)
    // ''    = public/ is the web server's document root (recommended on a real server)
    'base_url' => null,

    // Show PHP errors on screen. Set to false on the live server.
    'debug' => true,

    'timezone' => 'Asia/Bangkok',

    // Largest upload allowed for documents and images (MB).
    // PHP's own upload_max_filesize / post_max_size must also allow this.
    'upload_max_mb' => 20,
];
