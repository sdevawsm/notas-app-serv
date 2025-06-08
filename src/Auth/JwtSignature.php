<?php

namespace LadyPHP\Auth;

class JwtSignature
{
    /**
     * Algoritmos HMAC suportados
     */
    private const SUPPORTED_ALGORITHMS = [
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512'
    ];

    /**
     * Gera uma chave secreta segura
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function generateSecretKey(int $length = 32): string
    {
        if ($length < 32) {
            throw new \InvalidArgumentException('A chave secreta deve ter no mínimo 32 bytes');
        }

        // Gera bytes aleatórios criptograficamente seguros
        $bytes = random_bytes($length);
        
        // Converte para Base64Url
        return JwtToken::base64UrlEncode($bytes);
    }

    /**
     * Cria uma assinatura HMAC
     * @param string $header
     * @param string $payload
     * @param string $secret
     * @param string $algorithm
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function create(string $header, string $payload, string $secret, string $algorithm = 'HS256'): string
    {
        // Obtém o algoritmo HMAC correspondente
        $hmacAlgorithm = self::getHmacAlgorithm($algorithm);

        // Cria a mensagem a ser assinada (header.payload)
        $message = $header . '.' . $payload;

        // Cria a assinatura HMAC
        $signature = hash_hmac($hmacAlgorithm, $message, $secret, true);

        return $signature;
    }

    /**
     * Verifica se uma assinatura é válida
     * @param string $signature
     * @param string $header
     * @param string $payload
     * @param string $secret
     * @param string $algorithm
     * @return bool
     * @throws \InvalidArgumentException
     */
    public static function verify(string $signature, string $header, string $payload, string $secret, string $algorithm = 'HS256'): bool
    {
        // Cria uma nova assinatura com os mesmos parâmetros
        $expectedSignature = self::create($header, $payload, $secret, $algorithm);

        // Compara as assinaturas de forma segura contra timing attacks
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Retorna o algoritmo HMAC correspondente
     * @param string $algorithm
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getHmacAlgorithm(string $algorithm): string
    {
        if (!isset(self::SUPPORTED_ALGORITHMS[$algorithm])) {
            throw new \InvalidArgumentException(
                "Algoritmo não suportado: {$algorithm}. " .
                "Algoritmos suportados: " . implode(', ', array_keys(self::SUPPORTED_ALGORITHMS))
            );
        }

        return self::SUPPORTED_ALGORITHMS[$algorithm];
    }
} 