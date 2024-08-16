<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        $user_role=$request->user()->role;
        $roles_array=explode('|',$roles);
        if(in_array($user_role,$roles_array))
            return $next($request);
        throw new AuthorizationException();
    }
}
