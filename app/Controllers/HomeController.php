<?php

namespace App\Controllers;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class HomeController
{
    /**
     * Método que responde à rota raiz (/)
     */
    public function index(Request $request): Response
    {
        return new Response('', 302, [
            'Location' => '/index.html',
            'Content-Type' => 'text/html; charset=UTF-8'
        ]);
    }
} 