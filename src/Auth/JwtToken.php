<?php

namespace LadyPHP\Auth;

class JwtToken
{
    /**
     * Codifica uma string para Base64Url
     * @param string $data
     * @return string
     */
    public static function base64UrlEncode(string $data): string
    {
        // Converte para base64
        $base64 = base64_encode($data);
        
        // Remove caracteres não seguros para URL
        $base64Url = strtr($base64, '+/', '-_');
        
        // Remove padding
        return rtrim($base64Url, '=');
    }

    /**
     * Decodifica uma string de Base64Url
     * @param string $data
     * @return string
     */
    public static function base64UrlDecode(string $data): string
    {
        // Adiciona padding se necessário
        $padding = strlen($data) % 4;
        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }

        // Converte caracteres de volta
        $base64 = strtr($data, '-_', '+/');
        
        // Decodifica
        return base64_decode($base64);
    }

    /**
     * Gera um token JWT completo
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @return string
     */
    public static function generate(array $header, array $payload, string $secret): string
    {
        // Codifica header e payload
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // Cria a assinatura
        $signature = JwtSignature::create($headerEncoded, $payloadEncoded, $secret, $header['alg'] ?? 'HS256');
        $signatureEncoded = self::base64UrlEncode($signature);

        // Retorna o token completo
        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * Valida a estrutura básica de um token JWT
     * @param string $token
     * @return bool
     */
    public static function validateStructure(string $token): bool
    {
        // Verifica se o token tem exatamente 3 partes
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        // Verifica se cada parte é uma string Base64Url válida
        foreach ($parts as $part) {
            if (!self::isValidBase64Url($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica o formato do token
     * @param string $token
     * @return bool
     */
    public static function validateFormat(string $token): bool
    {
        if (!self::validateStructure($token)) {
            return false;
        }

        $parts = explode('.', $token);
        
        try {
            // Tenta decodificar o header
            $header = json_decode(self::base64UrlDecode($parts[0]), true, 512, JSON_THROW_ON_ERROR);
            
            // Verifica se o header tem os campos obrigatórios
            if (!isset($header['alg']) || !isset($header['typ']) || $header['typ'] !== 'JWT') {
                return false;
            }

            // Tenta decodificar o payload
            $payload = json_decode(self::base64UrlDecode($parts[1]), true, 512, JSON_THROW_ON_ERROR);
            
            // Verifica se o payload é um array
            if (!is_array($payload)) {
                return false;
            }

            return true;
        } catch (\JsonException $e) {
            return false;
        }
    }

    /**
     * Verifica se uma string é um Base64Url válido
     * @param string $data
     * @return bool
     */
    private static function isValidBase64Url(string $data): bool
    {
        // Verifica se contém apenas caracteres válidos
        if (!preg_match('/^[A-Za-z0-9\-_]+$/', $data)) {
            return false;
        }

        // Tenta decodificar
        try {
            self::base64UrlDecode($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
} 