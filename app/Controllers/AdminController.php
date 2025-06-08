<?php

namespace App\Controllers;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class AdminController {
    public function dashboard(Request $request): Response {
        return new Response(
            '<h1>Dashboard Admin</h1>' .
            '<p>Esta é a área administrativa.</p>' .
            '<p>Rota atual: ' . $request->getUri() . '</p>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    public function users(Request $request): Response {
        return new Response(
            '<h1>Gerenciamento de Usuários</h1>' .
            '<p>Lista de usuários do sistema.</p>' .
            '<p>Rota atual: ' . $request->getUri() . '</p>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
} 