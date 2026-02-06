<?php

namespace JanDev\SeoTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JanDev\SeoTools\Models\Redirect;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $redirect = Redirect::findByPath($request->getPathInfo());

        if ($redirect) {
            $redirect->incrementHits();

            return redirect($redirect->destination_path, $redirect->status_code);
        }

        return $next($request);
    }
}
