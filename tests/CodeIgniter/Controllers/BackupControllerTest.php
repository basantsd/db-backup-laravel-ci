<?php

namespace Basantsd\Backup\Tests\CodeIgniter\Controllers;

use PHPUnit\Framework\TestCase;
use Basantsd\Backup\CodeIgniter\Controllers\BackupController;
use Basantsd\Backup\Backup;
use Mockery;

class BackupControllerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testDailyBackupAuthorized()
    {
        $backupMock = Mockery::mock(Backup::class);
        $backupMock->shouldReceive('handle')->once();

        $controller = new BackupController();
        $controller->daily();

        $this->assertTrue(true);
    }

    public function testDailyBackupUnauthorized()
    {
        $_SERVER['HTTP_X_BACKUP_TOKEN'] = 'invalid-token';
        putenv('BACKUP_TOKEN=valid-token');

        $controller = new BackupController();
        $this->expectException(\CI_Exceptions::class);
        $this->expectExceptionCode(401);

        $controller->daily();
    }
}
