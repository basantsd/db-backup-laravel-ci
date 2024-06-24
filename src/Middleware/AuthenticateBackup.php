<?php

namespace Basantsd\Backup\Middleware;

use Closure;

class AuthenticateBackup
{
    public function handle($request, Closure $next)
    {
        if ($request->header('X-Backup-Token') !== config('backup.token')) {
            return response('Unauthorized', 401);
        }

        return $next($request);
    }
}
