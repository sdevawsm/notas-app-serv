# Documentação do Módulo JWT

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Estrutura do Módulo](#estrutura-do-módulo)
3. [Configuração](#configuração)
4. [Uso Básico](#uso-básico)
5. [Classes e Métodos](#classes-e-métodos)
6. [Middleware](#middleware)
7. [Rotas de Autenticação](#rotas-de-autenticação)
8. [Boas Práticas](#boas-práticas)
9. [Exemplos de Uso](#exemplos-de-uso)

## 🔍 Visão Geral

O módulo JWT implementa autenticação baseada em JSON Web Tokens (JWT) para APIs RESTful. Ele fornece uma solução completa para gerenciamento de tokens, incluindo geração, validação, renovação e revogação de tokens.

### Características Principais
- Geração e validação de tokens JWT
- Suporte a múltiplos algoritmos de assinatura (HS256, HS384, HS512)
- Gerenciamento de blacklist para tokens revogados
- Middleware para proteção de rotas
- Sistema de refresh token
- Claims personalizados
- Validação de expiração e claims padrão

## 📁 Estrutura do Módulo

```
src/Auth/
├── JwtAuth.php           # Serviço principal de JWT
├── JwtPayload.php        # Manipulação do payload
├── JwtToken.php          # Manipulação do token
└── JwtSignature.php      # Gerenciamento de assinaturas

src/Http/Middleware/
└── JwtAuthMiddleware.php # Middleware de autenticação
```

## ⚙️ Configuração

### Variáveis de Ambiente
```env
# Configurações do JWT
JWT_SECRET=sua-chave-secreta-aqui
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=604800
JWT_ISSUER=api
JWT_AUDIENCE=api-clients
```

### Configuração do Serviço
```php
use Framework\Auth\JwtAuth;

$jwtAuth = new JwtAuth(
    $_ENV['JWT_SECRET'],
    [
        'algorithm' => $_ENV['JWT_ALGORITHM'],
        'expiration' => (int)$_ENV['JWT_EXPIRATION'],
        'issuer' => $_ENV['JWT_ISSUER'],
        'audience' => $_ENV['JWT_AUDIENCE'],
        'blacklist_enabled' => true
    ]
);
```

## 🚀 Uso Básico

### Login e Geração de Token
```php
// Em seu AuthController
public function login(Request $request)
{
    $credentials = $request->getBody();
    
    // Validar credenciais...
    
    $token = $this->jwtAuth->login([
        'id' => $user->id,
        'email' => $user->email,
        'name' => $user->name
    ]);
    
    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
}
```

### Proteção de Rotas
```php
// Em routes/api.php
$router->group(['middleware' => 'jwt'], function($router) {
    $router->get('/user', 'UserController@show');
    $router->put('/user', 'UserController@update');
});
```

## 📚 Classes e Métodos

### JwtAuth

Classe principal que coordena todas as operações JWT.

#### Métodos Principais
- `login(array $userData, array $customClaims = []): string`
  - Gera um novo token JWT
  - Adiciona claims personalizados
  - Retorna o token assinado

- `logout(string $token): bool`
  - Invalida um token
  - Adiciona à blacklist se habilitada

- `refresh(string $token): ?string`
  - Atualiza um token expirado
  - Retorna novo token ou null

- `validate(string $token, bool $ignoreExpiration = false): ?array`
  - Valida um token
  - Retorna o payload ou null

### JwtPayload

Gerencia o payload do token JWT.

#### Claims Padrão
- `iss` (Issuer): Emissor do token
- `sub` (Subject): Assunto do token (geralmente ID do usuário)
- `aud` (Audience): Audiência do token
- `exp` (Expiration): Tempo de expiração
- `nbf` (Not Before): Tempo de início de validade
- `iat` (Issued At): Tempo de emissão
- `jti` (JWT ID): Identificador único do token

### JwtSignature

Gerencia a assinatura do token.

#### Algoritmos Suportados
- HS256 (SHA-256)
- HS384 (SHA-384)
- HS512 (SHA-512)

### JwtToken

Manipula a estrutura básica do token.

#### Métodos Principais
- `base64UrlEncode(string $data): string`
- `base64UrlDecode(string $data): string`
- `validateStructure(string $token): bool`
- `validateFormat(string $token): bool`

## 🔒 Middleware

### JwtAuthMiddleware

Middleware que protege rotas e gerencia autenticação.

#### Configuração
```php
$middleware = new JwtAuthMiddleware($jwtAuth, [
    '/api/auth/login',
    '/api/auth/register',
    '/api/public/*'
]);
```

#### Funcionalidades
- Verifica token no header Authorization
- Valida estrutura e assinatura
- Adiciona usuário à requisição
- Gerencia rotas públicas

## 🛣️ Rotas de Autenticação

### Rotas Recomendadas
```php
// Rotas públicas
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/register', 'AuthController@register');

// Rotas protegidas
$router->group(['middleware' => 'jwt'], function($router) {
    $router->post('/api/auth/logout', 'AuthController@logout');
    $router->post('/api/auth/refresh', 'AuthController@refresh');
    $router->get('/api/auth/me', 'AuthController@me');
});
```

## ✅ Boas Práticas

1. **Segurança**
   - Use HTTPS em produção
   - Gere chaves secretas fortes
   - Implemente rate limiting
   - Mantenha tokens com vida curta
   - Use refresh tokens com rotação

2. **Claims**
   - Inclua apenas dados necessários
   - Evite dados sensíveis
   - Use claims padrão quando apropriado
   - Valide todos os claims

3. **Blacklist**
   - Mantenha blacklist de tokens revogados
   - Implemente limpeza automática
   - Use cache para melhor performance

4. **Erros**
   - Retorne códigos HTTP apropriados
   - Forneça mensagens de erro claras
   - Registre tentativas de autenticação

## 📝 Exemplos de Uso

### Login e Autenticação
```php
// Login
$response = $http->post('/api/auth/login', [
    'email' => 'user@example.com',
    'password' => 'password123'
]);

$token = $response->json()['token'];

// Acesso a rota protegida
$response = $http->get('/api/user', [
    'Authorization' => 'Bearer ' . $token
]);
```

### Refresh Token
```php
$response = $http->post('/api/auth/refresh', [], [
    'Authorization' => 'Bearer ' . $oldToken
]);

$newToken = $response->json()['token'];
```

### Logout
```php
$response = $http->post('/api/auth/logout', [], [
    'Authorization' => 'Bearer ' . $token
]);
```

## 🔍 Debugging

### Verificar Token
```php
try {
    $payload = $jwtAuth->validate($token);
    if ($payload) {
        echo "Token válido\n";
        echo "Expira em: " . date('Y-m-d H:i:s', $payload['exp']) . "\n";
        echo "Usuário: " . $payload['sub'] . "\n";
    }
} catch (\Exception $e) {
    echo "Token inválido: " . $e->getMessage() . "\n";
}
```

### Verificar Blacklist
```php
if ($jwtAuth->isBlacklisted($token)) {
    echo "Token está na blacklist\n";
}
```

## ⚠️ Considerações de Segurança

1. **Chave Secreta**
   - Mantenha a chave secreta em variáveis de ambiente
   - Use chaves de pelo menos 32 bytes
   - Gere novas chaves periodicamente

2. **Tokens**
   - Mantenha tempo de expiração curto
   - Implemente refresh tokens
   - Revogue tokens em logout
   - Valide todos os claims

3. **Headers**
   - Use HTTPS
   - Implemente CORS corretamente
   - Valide origens de requisição

4. **Dados**
   - Não inclua dados sensíveis no token
   - Valide todos os inputs
   - Implemente rate limiting
   - Monitore tentativas de autenticação 