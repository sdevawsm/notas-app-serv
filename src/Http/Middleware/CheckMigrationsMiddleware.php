<?php

namespace LadyPHP\Http\Middleware;

use LadyPHP\Database\MigrationManager;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class CheckMigrationsMiddleware implements MiddlewareInterface {
    private MigrationManager $migrationManager;

    public function __construct(MigrationManager $migrationManager) {
        $this->migrationManager = $migrationManager;
    }

    public function handle(Request $request, callable $next): Response {
        // Se for uma requisição POST para /migrate, executa as migrations
        if ($request->getMethod() === 'POST' && $request->getUri() === '/migrate') {
            try {
                // Verifica se o banco de dados existe
                if (!$this->migrationManager->hasDatabase()) {
                    throw new \Exception('O banco de dados não existe. Por favor, crie o banco de dados primeiro.');
                }

                $this->migrationManager->runMigrations();
                return new Response(json_encode([
                    'success' => true,
                    'message' => 'Migrations executadas com sucesso!'
                ]), 200, ['Content-Type' => 'application/json']);
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $errorTrace = $e->getTraceAsString();
                return new Response(json_encode([
                    'success' => false,
                    'error' => $errorMessage,
                    'trace' => $errorTrace
                ]), 500, ['Content-Type' => 'application/json']);
            }
        }

        // Permite que a rota /adduser funcione mesmo com migrações pendentes
        if ($request->getUri() === '/adduser') {
            return $next($request);
        }

        // Se a tabela migrations não existe, mostra a mensagem de migrações pendentes
        if (!$this->migrationManager->hasTable('migrations')) {
            $pendingMigrations = $this->migrationManager->getPendingMigrations();
            $html = $this->getMigrationWarningHtml($pendingMigrations);
            return new Response($html, 503);
        }

        // Verifica se há migrações pendentes
        if ($this->migrationManager->hasPendingMigrations()) {
            $pendingMigrations = $this->migrationManager->getPendingMigrations();
            $html = $this->getMigrationWarningHtml($pendingMigrations);
            return new Response($html, 503);
        }

        return $next($request);
    }

    private function getMigrationWarningHtml(array $pendingMigrations): string {
        $migrationsList = implode('<br>', array_map(function($migration) {
            return "    - {$migration}";
        }, $pendingMigrations));

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Migrations Pendentes</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 20px;
                    background-color: #f5f5f5;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background-color: white;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #e74c3c;
                    margin-top: 0;
                }
                .migrations {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 4px;
                    margin: 15px 0;
                    font-family: monospace;
                }
                .command {
                    background-color: #2c3e50;
                    color: white;
                    padding: 10px;
                    border-radius: 4px;
                    font-family: monospace;
                    margin: 15px 0;
                }
                .button {
                    background-color: #3498db;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 16px;
                }
                .button:hover {
                    background-color: #2980b9;
                }
                .button:disabled {
                    background-color: #95a5a6;
                    cursor: not-allowed;
                }
                .loading {
                    display: none;
                    margin-top: 10px;
                    color: #3498db;
                }
                .error {
                    display: none;
                    margin-top: 10px;
                    padding: 10px;
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 4px;
                    color: #721c24;
                }
                .error pre {
                    background-color: #f8f9fa;
                    padding: 10px;
                    border-radius: 4px;
                    overflow-x: auto;
                    margin-top: 10px;
                }
                .success {
                    display: none;
                    margin-top: 10px;
                    padding: 10px;
                    background-color: #d4edda;
                    border: 1px solid #c3e6cb;
                    border-radius: 4px;
                    color: #155724;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>⚠️ Migrations Pendentes</h1>
                <p>Existem migrations que ainda não foram executadas no banco de dados.</p>
                
                <div class="migrations">
                    <strong>Migrations pendentes:</strong><br>
                    {$migrationsList}
                </div>

                <p>Para executar as migrations, você pode:</p>
                <div class="command">
                    php lady migrate
                </div>

                <p>Ou clicar no botão abaixo:</p>
                <button id="migrateButton" class="button" onclick="runMigrations()">Executar Migrations</button>
                <div id="loading" class="loading">Executando migrations...</div>
                <div id="error" class="error"></div>
                <div id="success" class="success"></div>
            </div>

            <script>
                async function runMigrations() {
                    const button = document.getElementById('migrateButton');
                    const loading = document.getElementById('loading');
                    const error = document.getElementById('error');
                    const success = document.getElementById('success');

                    button.disabled = true;
                    loading.style.display = 'block';
                    error.style.display = 'none';
                    success.style.display = 'none';

                    try {
                        const response = await fetch('/migrate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });

                        let data;
                        try {
                            const text = await response.text();
                            data = JSON.parse(text);
                        } catch (e) {
                            throw new Error('Erro ao processar resposta do servidor: ' + e.message + '\nResposta recebida: ' + text);
                        }

                        if (response.ok && data.success) {
                            success.textContent = data.message || 'Migrations executadas com sucesso!';
                            success.style.display = 'block';
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            error.innerHTML = '<strong>Erro ao executar migrations:</strong><br>' +
                                (data.error || 'Erro desconhecido') + '<br>' +
                                '<pre>' + (data.trace || '') + '</pre>';
                            error.style.display = 'block';
                            button.disabled = false;
                        }
                    } catch (e) {
                        error.innerHTML = '<strong>Erro ao executar migrations:</strong><br>' +
                            e.message + '<br>' +
                            '<pre>' + e.stack + '</pre>';
                        error.style.display = 'block';
                        button.disabled = false;
                    } finally {
                        loading.style.display = 'none';
                    }
                }
            </script>
        </body>
        </html>
        HTML;
    }
} 