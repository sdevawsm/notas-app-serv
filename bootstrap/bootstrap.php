<?php

use LadyPHP\Core\Application;

// Cria a instância da aplicação
$app = new Application();

// Carrega as configurações
$app->make('config');

// Retorna a instância da aplicação
return $app; 