# db-backup-laravel-ci

A framework agnostic package to automatically back up your database and send the result via email. It supports both Laravel and CodeIgniter applications.

## Features
- Works with MySQL, PostgreSQL, SQL Server and SQLite databases
- Creates a SQL dump and compresses it using `gzip`
- Emails the compressed dump on a daily schedule
- If the gzipped file exceeds the configured size limit (25&nbsp;MB by default) it is saved to disk and a download path is emailed instead

## Installation
```bash
composer require basantsd/db-backup-laravel-ci
```

## Environment variables
Add these entries to your `.env` file:
```env
BACKUP_EMAIL=your-email@example.com
BACKUP_TOKEN=your-secret-token
APP_NAME="project-name"
# Optional: override the maximum attachment size in bytes (default 26214400)
BACKUP_MAX_ATTACHMENT_SIZE=26214400
```

## Laravel setup
1. Publish the configuration file:
   ```bash
   php artisan vendor:publish --provider="Basantsd\\Backup\\Laravel\\BackupServiceProvider" --tag="config"
   ```
2. Schedule the command in `app/Console/Kernel.php`:
   ```php
   protected function schedule(Schedule $schedule)
   {
       $schedule->command('backup:daily', ['--token' => config('backup.token')])->daily();
   }
   ```

## CodeIgniter setup
1. Add a route to `routes.php`:
   ```php
   $route['backup/daily'] = 'BackupController/daily';
   ```
2. Call the route with the token header:
   ```bash
   curl -H "X-Backup-Token: your-secret-token" http://your-domain.com/backup/daily
   ```

## Usage
### Laravel
Run the artisan command:
```bash
php artisan backup:daily --token=your-secret-token
```

### CodeIgniter
Access the `/backup/daily` URL with the header shown above.

## Handling large backups
Many email providers reject attachments larger than 20&ndash;25&nbsp;MB. When the compressed backup exceeds `BACKUP_MAX_ATTACHMENT_SIZE`, this package saves the file to a `backups` directory (`storage/app/backups` for Laravel or `APPPATH/backups` for CodeIgniter) and sends an email containing the path to download the backup manually.

## License
The MIT License (MIT). Please see the LICENSE file for more information.
