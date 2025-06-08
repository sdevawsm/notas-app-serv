<?php

namespace LadyPHP\Auth;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class JwtAuth
{
    /**
     * Configuração padrão
     */
    private const DEFAULT_CONFIG = [
        'algorithm' => 'HS256',
        'expiration' => 3600, // 1 hora
        'issuer' => 'framework',
        'audience' => 'api',
        'blacklist_enabled' => true
    ];

    /**
     * @var array Configuração do JWT
     */
    private array $config;

    /**
     * @var string Chave secreta para assinatura
     */
    private string $secret;

    /**
     * @var array Lista negra de tokens
     */
    private array $blacklist = [];

    /**
     * @var array|null Usuário atual
     */
    private ?array $currentUser = null;

    /**
     * @param string $secret Chave secreta para assinatura
     * @param array $config Configurações personalizadas
     */
    public function __construct(string $secret, array $config = [])
    {
        $this->secret = $secret;
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
    }

    /**
     * Realiza login e retorna o token JWT
     * @param array $userData Dados do usuário
     * @param array $customClaims Claims personalizados
     * @return string Token JWT
     */
    public function login(array $userData, array $customClaims = []): string
    {
        // Prepara o payload com dados do usuário
        $payload = JwtPayload::create(
            $userData,
            $this->config['expiration'],
            [
                'iss' => $this->config['issuer'],
                'aud' => $this->config['audience'],
                'sub' => $userData['id'] ?? null
            ]
        );

        // Adiciona claims personalizados
        if (!empty($customClaims)) {
            $payload = JwtPayload::addClaims($payload, $customClaims);
        }

        // Gera o token
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->config['algorithm']]);
        $payload = json_encode($payload);
        
        // Cria a assinatura
        $signature = JwtSignature::create(
            JwtToken::base64UrlEncode($header),
            JwtToken::base64UrlEncode($payload),
            $this->secret,
            $this->config['algorithm']
        );

        // Retorna o token completo
        return JwtToken::base64UrlEncode($header) . '.' .
               JwtToken::base64UrlEncode($payload) . '.' .
               JwtToken::base64UrlEncode($signature);
    }

    /**
     * Realiza logout invalidando o token
     * @param string $token Token a ser invalidado
     * @return bool
     */
    public function logout(string $token): bool
    {
        if (!$this->config['blacklist_enabled']) {
            return true;
        }

        try {
            $payload = $this->validate($token);
            if ($payload && isset($payload['jti'])) {
                $this->blacklist[$payload['jti']] = $payload['exp'];
                return true;
            }
        } catch (\Exception $e) {
            // Token inválido, não precisa adicionar à blacklist
        }

        return false;
    }

    /**
     * Atualiza um token expirado
     * @param string $token Token atual
     * @return string|null Novo token ou null se não puder ser atualizado
     */
    public function refresh(string $token): ?string
    {
        try {
            $payload = $this->validate($token, true); // true para ignorar expiração
            if (!$payload) {
                return null;
            }

            // Remove claims que não devem ser renovados
            unset($payload['iat'], $payload['exp'], $payload['nbf'], $payload['jti']);

            // Realiza novo login com os dados do payload
            return $this->login($payload);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Valida um token JWT
     * @param string $token Token a ser validado
     * @param bool $ignoreExpiration Ignorar verificação de expiração
     * @return array|null Payload decodificado ou null se inválido
     */
    public function validate(string $token, bool $ignoreExpiration = false): ?array
    {
        try {
            // Valida estrutura do token
            if (!JwtToken::validateStructure($token)) {
                return null;
            }

            // Decodifica as partes do token
            [$headerB64, $payloadB64, $signatureB64] = explode('.', $token);
            
            $header = json_decode(JwtToken::base64UrlDecode($headerB64), true);
            $payload = json_decode(JwtToken::base64UrlDecode($payloadB64), true);
            $signature = JwtToken::base64UrlDecode($signatureB64);

            // Valida algoritmo
            if (!isset($header['alg']) || $header['alg'] !== $this->config['algorithm']) {
                return null;
            }

            // Verifica assinatura
            if (!JwtSignature::verify(
                $signature,
                $headerB64,
                $payloadB64,
                $this->secret,
                $this->config['algorithm']
            )) {
                return null;
            }

            // Verifica blacklist
            if ($this->config['blacklist_enabled'] && 
                isset($payload['jti']) && 
                isset($this->blacklist[$payload['jti']])) {
                return null;
            }

            // Valida payload
            if (!$ignoreExpiration && !JwtPayload::validate($payload)) {
                return null;
            }

            // Armazena usuário atual
            $this->currentUser = $payload;

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retorna o usuário atual
     * @return array|null
     */
    public function getCurrentUser(): ?array
    {
        return $this->currentUser;
    }

    /**
     * Adiciona um token à lista negra
     * @param string $token Token a ser invalidado
     * @return bool
     */
    public function blacklist(string $token): bool
    {
        return $this->logout($token);
    }

    /**
     * Verifica se um token está na lista negra
     * @param string $token Token a ser verificado
     * @return bool
     */
    public function isBlacklisted(string $token): bool
    {
        if (!$this->config['blacklist_enabled']) {
            return false;
        }

        try {
            $payload = $this->validate($token, true);
            return $payload && isset($payload['jti']) && isset($this->blacklist[$payload['jti']]);
        } catch (\Exception $e) {
            return false;
        }
    }
} 