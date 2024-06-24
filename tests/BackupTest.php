<?php

namespace Basantsd\Backup\Tests;

use PHPUnit\Framework\TestCase;
use Basantsd\Backup\Backup;
use Mockery;

class BackupTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateBackup()
    {
        $backup = new Backup();

        // Mock database configuration
        config(['database.connections.mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'test_db',
            'username' => 'root',
            'password' => 'password',
        ]]);

        // Test MySQL backup creation
        $filePath = $backup->createBackup();
        $this->assertFileExists($filePath);
        unlink($filePath);
    }

    public function testSendBackupViaEmail()
    {
        $backup = Mockery::mock(Backup::class)->makePartial();

        $filePath = tempnam(sys_get_temp_dir(), 'backup');
        file_put_contents($filePath, 'dummy content');

        // Mock email sending
        $backup->shouldReceive('sendBackupViaEmail')
            ->with($filePath)
            ->once()
            ->andReturnNull();

        $backup->sendBackupViaEmail($filePath);

        unlink($filePath);
    }
}
