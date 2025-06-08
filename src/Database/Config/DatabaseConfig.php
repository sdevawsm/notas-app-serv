<?php

namespace LadyPHP\Database\Config;

use PDO;

class DatabaseConfig
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Inicializa as configurações do banco de dados
     */
    public static function initialize(): void
    {
        self::load();
    }

    /**
     * Carrega as configurações do arquivo .env e usa config/database.php como fallback
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Carrega configurações do config/database.php como fallback
        $databaseConfig = require __DIR__ . '/../../../config/database.php';
        $defaultConfig = $databaseConfig['connections']['mysql'] ?? [];

        // Tenta carregar do .env
        $envFile = __DIR__ . '/../../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove aspas se existirem
                    if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$config[$key] = $value;
                }
            }
        }

        // Mapeia as configurações do .env para as chaves do config/database.php
        $configMap = [
            'DB_CONNECTION' => 'driver',
            'DB_HOST' => 'host',
            'DB_PORT' => 'port',
            'DB_DATABASE' => 'database',
            'DB_USERNAME' => 'username',
            'DB_PASSWORD' => 'password'
        ];

        // Usa valores do .env se disponíveis, senão usa os valores padrão do config/database.php
        foreach ($configMap as $envKey => $configKey) {
            $value = self::$config[$envKey] ?? null;
            if (empty($value) && isset($defaultConfig[$configKey])) {
                self::$config[$envKey] = $defaultConfig[$configKey];
            }
        }

        // Garante que as configurações essenciais existam
        self::$config['DB_CONNECTION'] = self::$config['DB_CONNECTION'] ?? $defaultConfig['driver'] ?? 'mysql';
        self::$config['DB_HOST'] = self::$config['DB_HOST'] ?? $defaultConfig['host'] ?? 'localhost';
        self::$config['DB_PORT'] = self::$config['DB_PORT'] ?? $defaultConfig['port'] ?? '3306';
        self::$config['DB_DATABASE'] = self::$config['DB_DATABASE'] ?? $defaultConfig['database'] ?? '';
        self::$config['DB_USERNAME'] = self::$config['DB_USERNAME'] ?? $defaultConfig['username'] ?? 'root';
        self::$config['DB_PASSWORD'] = self::$config['DB_PASSWORD'] ?? $defaultConfig['password'] ?? '';

        self::$loaded = true;
    }

    /**
     * Retorna uma configuração específica
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config[$key] ?? $default;
    }

    /**
     * Retorna a string de conexão DSN para o PDO
     */
    public static function getDsn(): string
    {
        if (!self::$loaded) {
            self::load();
        }

        $driver = self::get('DB_CONNECTION', 'mysql');
        $host = self::get('DB_HOST', 'localhost');
        $port = self::get('DB_PORT', '3306');
        $database = self::get('DB_DATABASE');

        if (!$database) {
            throw new \Exception('Database name not configured');
        }

        return "{$driver}:host={$host};port={$port};dbname={$database}";
    }

    /**
     * Retorna as credenciais do banco de dados
     */
    public static function getCredentials(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return [
            'username' => self::get('DB_USERNAME', 'root'),
            'password' => self::get('DB_PASSWORD', '')
        ];
    }

    /**
     * Retorna as opções padrão do PDO
     */
    public static function getPdoOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
    }
} 