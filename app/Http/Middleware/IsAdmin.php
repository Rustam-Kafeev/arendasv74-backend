<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
   public function handle(Request $request, Closure $next): Response
{
    if (!Auth::check()) {
        dd('Не авторизован');
    }
    if (!Auth::user()->is_admin) {
        dd('Не администратор');
    }
    return $next($request);
}
}