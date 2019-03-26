<?php

namespace FastDog\Core\Http\Middleware;

use Closure;

/**
 * Class Admin
 *
 * @package FastDog\Core\Http\Middleware
 * @version 0.1.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Admin
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Auth::check();

        if (!\Auth::guest()) {
            /**
             * @var $user User
             */
            $user = \Auth::user();

            if ($user->type <> 'admin') {
                return ($request->ajax()) ? response()->json(['error' => 'Access denied.'], 401) :
                    redirect()->guest('/');
            }
        } else {
            return ($request->ajax()) ? response()->json(['error' => 'Unauthorized.'], 401) :
                response('Unauthorized.', 401);
        }

        return $next($request);
    }

}
