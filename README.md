# db-backup-laravel-ci
A Laravel and CI package for daily database backups sent via email



## Installation

You can install the package via Composer:

```bash
composer require basantsd/db-backup-laravel-ci
```

## Configuration

Add the following to your .env file:

```
BACKUP_EMAIL=your-email@example.com
BACKUP_TOKEN=your-secret-token
APP_NAME="project-name"
```

### Laravel Configuration

You can publish the configuration file:

```bash

php artisan vendor:publish --provider="Basantsd\\Backup\\Laravel\\BackupServiceProvider" --tag="config"
```

This will create a config/backup.php file where you can configure the email address and token.

Add the following to your ```app/Console/Kernel.php:```

```php

protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:daily', ['--token' => config('backup.token')])->daily();
}
```

### CodeIgniter Configuration

Load the BackupController in your ```routes.php```:

```php
$route['backup/daily'] = 'BackupController/daily';
```

Ensure to pass the token in the header when accessing the URL:

```bash
curl -H "X-Backup-Token: your-secret-token" http://your-domain.com/backup/daily
```

## Usage
### Laravel

Run the following command to perform a daily backup:

```bash
php artisan backup:daily --token=your-secret-token
```

### CodeIgniter

Access the backup URL with the token in the header to perform a daily backup:

```bash
curl -H "X-Backup-Token: your-secret-token" http://your-domain.com/backup/daily
```

## License

The MIT License (MIT). Please see the License File for more information.

csharp


This `README.md` file should now render correctly on GitHub and other markdown viewers, with all sections properly formatted and no breaks in the code sections.
