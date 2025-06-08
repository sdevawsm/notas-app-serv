<?php

namespace App\Middleware;

use LadyPHP\Http\Middleware\MiddlewareInterface;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class CacheMiddleware implements \LadyPHP\Http\Middleware\MiddlewareInterface {
    private string $cacheDir;
    private int $ttl; // Time to live em segundos

    public function __construct(string $cacheDir = 'cache', int $ttl = 3600) {
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->ttl = $ttl;
        
        // Cria o diretório de cache se não existir
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function handle(Request $request, callable $next): Response {
        // Só cache GET requests
        if ($request->getMethod() !== 'GET') {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request);
        $cacheFile = "{$this->cacheDir}/{$cacheKey}";

        // Tenta recuperar do cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->ttl)) {
            $cached = unserialize(file_get_contents($cacheFile));
            if ($cached instanceof Response) {
                return $cached;
            }
        }

        // Se não estiver em cache ou expirou, processa a requisição
        $response = $next($request);

        // Salva no cache se for uma resposta bem-sucedida
        if ($response->getStatusCode() === 200) {
            file_put_contents($cacheFile, serialize($response));
        }

        return $response;
    }

    private function generateCacheKey(Request $request): string {
        return md5($request->getMethod() . $request->getUri() . serialize($request->all()));
    }
} 