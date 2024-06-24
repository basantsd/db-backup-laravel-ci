<?php

namespace Basantsd\Backup\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Basantsd\Backup\Backup;

class DailyBackup extends Command
{
    protected $signature = 'backup:daily {--token=}';

    protected $description = 'Perform a daily database backup and send it via email';

    protected $backup;

    public function __construct(Backup $backup)
    {
        parent::__construct();
        $this->backup = $backup;
    }

    public function handle()
    {
        if ($this->option('token') !== config('backup.token')) {
            $this->error('Unauthorized');
            return;
        }

        $this->backup->handle();
        $this->info('Backup has been sent to your email.');
    }
}
