<?php

namespace Basantsd\Backup\Laravel;

use Illuminate\Support\ServiceProvider;
use Basantsd\Backup\Backup;

class BackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/backup.php' => config_path('backup.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\DailyBackup::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/backup.php', 'backup'
        );

        $this->app->singleton('backup', function ($app) {
            return new Backup();
        });
    }
}
