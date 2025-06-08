<?php

namespace LadyPHP\Http\Middleware;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class JWTMiddleware {
    public function handle(Request $request, callable $next): Response {}
}
