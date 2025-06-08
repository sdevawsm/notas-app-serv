<?php

namespace LadyPHP\View;

use LadyPHP\Http\Response;

class View
{
    private static ?ElleCompiler $compiler = null;
    private static string $viewPath;
    private static string $cachePath;

    public static function init(string $viewPath, string $cachePath): void
    {
        self::$viewPath = $viewPath;
        self::$cachePath = $cachePath;
        self::$compiler = new ElleCompiler($viewPath, $cachePath);
    }

    public static function render(string $view, array $data = []): Response
    {
        if (self::$compiler === null) {
            throw new \Exception('View não foi inicializada. Chame View::init() primeiro.');
        }

        error_log("View::render - Dados recebidos: " . print_r($data, true));
        $compiledFile = self::$compiler->compile($view, $data);
        
        ob_start();
        // Cria uma função anônima para encapsular o contexto
        $renderView = function($__file, $__data) {
            error_log("Renderizando view com dados: " . print_r($__data, true));
            extract($__data);
            include $__file;
        };
        
        // Executa a função com o arquivo compilado e os dados
        $renderView($compiledFile, $data);
        $content = ob_get_clean();

        return new Response($content);
    }
} 