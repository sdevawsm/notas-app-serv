<?php

namespace LadyPHP\Auth;

class JwtPayload
{
    /**
     * Claims padrão do JWT
     */
    private const DEFAULT_CLAIMS = [
        'iss', // Issuer (emissor)
        'sub', // Subject (assunto)
        'aud', // Audience (audiência)
        'exp', // Expiration Time (tempo de expiração)
        'nbf', // Not Before (não válido antes de)
        'iat', // Issued At (emitido em)
        'jti'  // JWT ID (identificador único)
    ];

    /**
     * Cria um payload padrão com claims básicos
     * @param array $data Dados do usuário/claims personalizados
     * @param int $expiration Tempo de expiração em segundos
     * @param array $options Opções adicionais para claims
     * @return array
     */
    public static function create(array $data, int $expiration, array $options = []): array
    {
        $now = time();
        
        // Claims padrão
        $payload = [
            'iat' => $now,                    // Emitido em
            'nbf' => $now,                    // Válido a partir de
            'exp' => $now + $expiration,      // Expira em
            'jti' => self::generateJti()      // ID único do token
        ];

        // Adiciona claims opcionais se fornecidos
        if (isset($options['iss'])) $payload['iss'] = $options['iss'];
        if (isset($options['sub'])) $payload['sub'] = $options['sub'];
        if (isset($options['aud'])) $payload['aud'] = $options['aud'];

        // Adiciona dados personalizados
        return array_merge($payload, $data);
    }

    /**
     * Valida o payload e seus claims
     * @param array $payload
     * @return bool
     */
    public static function validate(array $payload): bool
    {
        // Verifica claims obrigatórios
        if (!self::validateRequiredClaims($payload, ['iat', 'exp', 'nbf'])) {
            return false;
        }

        // Verifica se o token expirou
        if (self::isExpired($payload)) {
            return false;
        }

        // Verifica se o token ainda não é válido (nbf)
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            return false;
        }

        return true;
    }

    /**
     * Adiciona claims personalizados ao payload
     * @param array $payload
     * @param array $claims
     * @return array
     */
    public static function addClaims(array $payload, array $claims): array
    {
        // Filtra claims reservados
        $filteredClaims = array_filter($claims, function($key) {
            return !in_array($key, self::DEFAULT_CLAIMS);
        }, ARRAY_FILTER_USE_KEY);

        return array_merge($payload, $filteredClaims);
    }

    /**
     * Verifica claims obrigatórios no payload
     * @param array $payload
     * @param array $requiredClaims
     * @return bool
     */
    public static function validateRequiredClaims(array $payload, array $requiredClaims): bool
    {
        foreach ($requiredClaims as $claim) {
            if (!isset($payload[$claim])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verifica se o token expirou
     * @param array $payload
     * @return bool
     */
    public static function isExpired(array $payload): bool
    {
        if (!isset($payload['exp'])) {
            return true; // Se não tem expiração, considera expirado
        }

        return $payload['exp'] < time();
    }

    /**
     * Gera um JWT ID único
     * @return string
     */
    private static function generateJti(): string
    {
        // Gera um ID único usando timestamp + random bytes
        $uniqueId = uniqid('', true) . bin2hex(random_bytes(16));
        
        // Converte para Base64Url para garantir caracteres seguros
        return JwtToken::base64UrlEncode($uniqueId);
    }
} 