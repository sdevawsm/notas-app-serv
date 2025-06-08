# Guia de Implementação de Autenticação JWT

Este guia detalha o passo a passo para implementar nossa própria autenticação JWT no framework, sem dependências externas.

## 1. Estrutura de Arquivos

```
app/
├── Auth/
│   ├── JwtAuth.php           # Serviço principal de JWT
│   ├── JwtPayload.php        # Classe para manipulação do payload
│   ├── JwtToken.php          # Classe para manipulação do token
│   └── JwtSignature.php      # Classe para gerenciamento de assinaturas
├── Middleware/
│   └── JwtAuthMiddleware.php # Middleware de autenticação
├── Controllers/
│   └── AuthController.php    # Controlador de autenticação
└── Models/
    └── User.php             # Modelo de usuário (se necessário)
```

## 2. Implementação do Serviço JWT

### 2.1. Classe JwtToken
- Implementar métodos para:
  - Codificação Base64Url
  - Decodificação Base64Url
  - Geração de token (header + payload + signature)
  - Validação de estrutura do token
  - Verificação de formato

### 2.2. Classe JwtPayload
- Implementar métodos para:
  - Criação de payload
  - Validação de payload
  - Gerenciamento de claims
  - Verificação de claims obrigatórios
  - Manipulação de expiração

### 2.3. Classe JwtSignature
- Implementar métodos para:
  - Geração de chave secreta
  - Criação de assinatura HMAC
  - Verificação de assinatura
  - Suporte a diferentes algoritmos (HS256, HS384, HS512)

### 2.4. Classe JwtAuth
- Implementar métodos para:
  - Login (gerar token)
  - Logout (invalidar token)
  - Refresh token
  - Verificar autenticação
  - Obter usuário atual
  - Gerenciar blacklist de tokens

## 3. Implementação do Middleware

### 3.1. JwtAuthMiddleware
- Verificar token no header Authorization
- Validar estrutura do token
- Verificar assinatura
- Validar payload e claims
- Verificar expiração
- Adicionar usuário ao request
- Tratar erros de autenticação

## 4. Implementação do Controlador

### 4.1. AuthController
- Implementar endpoints:
  - POST /auth/login
  - POST /auth/logout
  - POST /auth/refresh
  - GET /auth/me (informações do usuário)

## 5. Configuração

### 5.1. Arquivo de Configuração
Criar `config/jwt.php`:
```php
return [
    'secret' => env('JWT_SECRET'),
    'algorithm' => 'HS256',
    'expiration' => 3600, // 1 hora
    'refresh_expiration' => 604800, // 7 dias
    'issuer' => env('APP_NAME', 'seu-app'),
    'audience' => env('APP_URL', 'seu-app-users'),
    'blacklist_enabled' => true,
    'blacklist_grace_period' => 30, // segundos
    'token_prefix' => 'Bearer'
];
```

## 6. Rotas

### 6.1. Definir Rotas de Autenticação
```php
$router->post('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/logout', [AuthController::class, 'logout'])->middleware('jwt');
$router->post('/auth/refresh', [AuthController::class, 'refresh']);
$router->get('/auth/me', [AuthController::class, 'me'])->middleware('jwt');
```

## 7. Implementação de Segurança

### 7.1. Geração de Chave Secreta
- Implementar geração segura de chave usando random_bytes
- Armazenar chave em variável de ambiente
- Implementar rotação de chaves

### 7.2. Blacklist de Tokens
- Implementar sistema de blacklist para tokens revogados
- Usar cache para armazenamento eficiente
- Implementar limpeza automática de tokens expirados

### 7.3. Claims Padrão
- Implementar validação de claims padrão (iat, exp, nbf, iss, aud)
- Adicionar claims personalizados conforme necessidade
- Implementar validação de sub (subject)

## 8. Exemplo de Uso

### 8.1. Login
```php
// AuthController.php
public function login(Request $request, Response $response)
{
    $credentials = $request->getBody();
    // Validar credenciais
    $token = $this->jwtAuth->login($credentials);
    return $response->json(['token' => $token]);
}
```

### 8.2. Proteger Rota
```php
$router->get('/api/protected', [SomeController::class, 'protectedMethod'])
    ->middleware('jwt');
```

### 8.3. Obter Usuário Autenticado
```php
// Em qualquer controller
public function someMethod(Request $request)
{
    $user = $request->getAttribute('user');
    // Usar dados do usuário
}
```

## 9. Boas Práticas

1. Sempre usar HTTPS em produção
2. Implementar rate limiting para endpoints de autenticação
3. Implementar blacklist de tokens revogados
4. Usar refresh tokens com rotação
5. Implementar logout em todos os dispositivos
6. Validar todos os inputs
7. Implementar logging de tentativas de autenticação
8. Usar senhas fortes e hash seguro
9. Implementar recuperação de senha
10. Implementar autenticação em dois fatores (opcional)

## 10. Testes

1. Testes unitários para:
   - Geração e validação de token
   - Manipulação de payload
   - Geração e verificação de assinatura
   - Blacklist de tokens
   - Middleware
2. Testes de integração para:
   - Fluxo de login
   - Fluxo de logout
   - Rotas protegidas
   - Refresh token
3. Testes de segurança:
   - Tokens expirados
   - Tokens inválidos
   - Tokens manipulados
   - Rate limiting
   - CSRF protection

## 11. Documentação

1. Documentar todos os endpoints da API
2. Documentar formato do token
3. Documentar claims personalizados
4. Documentar erros e códigos de status
5. Documentar fluxo de autenticação
6. Documentar configurações
7. Documentar boas práticas de segurança

## 12. Próximos Passos

1. Implementar sistema de usuários
2. Implementar roles e permissões
3. Implementar refresh token rotation
4. Implementar blacklist de tokens
5. Implementar rate limiting
6. Implementar logging
7. Implementar recuperação de senha
8. Implementar autenticação em dois fatores
9. Implementar testes
10. Documentar API 