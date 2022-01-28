<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class comprobarVendedor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user;

        if ($user->roles == "Particular" || $user->roles == "Profesional") {
            return $next($request);
        } else {
            $answer['msg'] = "You must be a particular or a profesional";
        }

        return response()-> json($answer);
    }
}
