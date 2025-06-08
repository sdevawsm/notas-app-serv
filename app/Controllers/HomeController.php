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
        return new Response(
            '<h1>Bem-vindo ao LadyPHP Framework!</h1>' .
            '<p>Esta é a página inicial do seu framework.</p>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
} 