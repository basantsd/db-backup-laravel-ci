<?php

namespace Basantsd\Backup\Tests\Laravel\Middleware;

use Illuminate\Http\Request;
use Basantsd\Backup\Middleware\AuthenticateBackup;
use PHPUnit\Framework\TestCase;

class AuthenticateBackupTest extends TestCase
{
    public function testMiddlewareAllowsValidToken()
    {
        $middleware = new AuthenticateBackup();

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_BACKUP_TOKEN' => 'valid-token']);
        config(['backup.token' => 'valid-token']);

        $response = $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMiddlewareBlocksInvalidToken()
    {
        $middleware = new AuthenticateBackup();

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_BACKUP_TOKEN' => 'invalid-token']);
        config(['backup.token' => 'valid-token']);

        $response = $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals(401, $response->getStatusCode());
    }
}
