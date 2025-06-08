<?php

namespace LadyPHP\Http\Middleware;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

interface MiddlewareInterface {
    /**
     * Processa a requisição e retorna uma resposta
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
} 