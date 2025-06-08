<?php

namespace Http\Middleware;

use Http\Request;
use Http\Response;

class AuthMiddleware {
    public function handle(Request $request, callable $next): Response {}
}
