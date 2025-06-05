<?php

return [
    'email' => env('BACKUP_EMAIL', 'your-email@example.com'),
    'token' => env('BACKUP_TOKEN', 'your-secret-token'),
    'project_name' => env('APP_NAME', 'project-name'),
    // Maximum attachment size in bytes; backups larger than this will be saved
    // to disk instead of being attached to the email.
    'max_attachment_size' => env('BACKUP_MAX_ATTACHMENT_SIZE', 25 * 1024 * 1024),
];
