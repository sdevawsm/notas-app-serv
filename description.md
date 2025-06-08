# Mini Framework PHP - Estrutura e Métodos

Este documento descreve a estrutura completa do mini framework PHP inspirado no Laravel, incluindo todos os métodos e responsabilidades de cada classe, além de melhorias arquiteturais para maior flexibilidade, escalabilidade e testabilidade.

## 🏗️ **Melhorias Arquiteturais**

- **Service Layer**: Camada para lógica de negócio em `app/Services/`.
- **Repository Layer**: Abstração de acesso a dados em `app/Repositories/`.
- **Container de Injeção de Dependências**: Resolve dependências automaticamente.
- **Service Providers**: Inicialização e registro de serviços customizados.
- **Sistema de Eventos**: Permite acoplar funcionalidades via eventos/observers.
- **Sistema de Logging**: Centralização de logs e integração com sistemas externos.
- **Sistema de Cache**: Drivers para file, array, redis, etc.
- **Configuração por Ambiente**: Uso de `.env` para variáveis de ambiente.
- **Middlewares Globais e Pipeline**: Execução de middlewares em todas as requisições.
- **Testabilidade**: Estrutura pensada para facilitar testes unitários e de integração.

## 📦 **Estrutura de Diretórios (Expandida)**

```
mini-framework/
├── app/
│   ├── Controllers/
│   │   └── UserController.php
│   ├── Services/         # Nova camada de serviços
│   ├── Repositories/     # Nova camada de repositórios
│   ├── Middleware/
│   └── Events/           # Eventos customizados
├── config/
│   └── app.php
├── public/
│   └── index.php
├── routes/
│   ├── web.php
│   └── api.php
├── src/
│   ├── Core/
│   │   ├── Application.php
│   │   ├── Container.php         # Novo: DI Container
│   │   ├── ServiceProvider.php   # Novo: Service Provider base
│   │   ├── EventDispatcher.php   # Novo: Sistema de eventos
│   │   ├── Logger.php            # Novo: Logger
│   ├── Http/
│   ├── Cache/                    # Novo: drivers de cache
│   └── Support/
├── tests/
├── vendor/
├── composer.json
└── README.md
```

## 📁 **src/Core/Container.php**
**Responsabilidade:** Resolver e injetar dependências automaticamente.

### Métodos
- `bind($abstract, $concrete)` - Registra um binding.
- `make($abstract)` - Resolve e instancia dependências.

## 📁 **src/Core/ServiceProvider.php**
**Responsabilidade:** Registrar e inicializar serviços customizados.

### Métodos
- `register()` - Registra serviços no container.
- `boot()` - Inicializa serviços após registro.

## 📁 **src/Core/EventDispatcher.php**
**Responsabilidade:** Gerenciar eventos e listeners.

### Métodos
- `listen($event, $listener)` - Registra listener para evento.
- `dispatch($event, $payload)` - Dispara evento para listeners.

## 📁 **src/Core/Logger.php**
**Responsabilidade:** Centralizar logs da aplicação.

### Métodos
- `info($message)` - Log informativo.
- `error($message)` - Log de erro.
- `debug($message)` - Log de debug.

## 📁 **src/Core/Cache/**
**Responsabilidade:** Drivers de cache (file, array, redis, etc).

### Métodos (exemplo para FileCache)
- `get($key)` - Obtém valor do cache.
- `set($key, $value, $ttl)` - Define valor no cache.
- `delete($key)` - Remove valor do cache.

## 📁 **app/Services/**
**Responsabilidade:** Lógica de negócio (ex: UserService, AuthService).

## 📁 **app/Repositories/**
**Responsabilidade:** Abstração de acesso a dados (ex: UserRepository).

## 📁 **.env**
**Responsabilidade:** Variáveis de ambiente para configuração por ambiente.

## 📁 **app/Events/**
**Responsabilidade:** Definição de eventos customizados.

## 📁 **src/Http/Middleware/**
**Responsabilidade:** Middlewares customizáveis, incluindo globais.

## 📁 **tests/**
**Responsabilidade:** Testes unitários e de integração.

## 🛠️ **Recursos Técnicos (Atualizados)**

### ⚡ **Performance**
- Singleton pattern para Application
- Lazy loading de componentes
- Cache de rotas e dados
- Otimização de autoload

### 🔧 **Configuração**
- Arquivos de configuração centralizados
- Configuração por ambiente com `.env`
- Variáveis de ambiente
- Configuração de JWT

### 📊 **Debugging e Logs**
- Tratamento de exceções
- Logs de erro centralizados
- Debug mode
- Stack trace detalhado

### 🔄 **Extensibilidade**
- Sistema de middleware extensível (incluindo globais)
- Controllers customizáveis
- Helpers globais
- Service providers
- Sistema de eventos
- Camadas Service e Repository

### 🧪 **Testabilidade**
- Estrutura para testes unitários e mocks
- Injeção de dependências para facilitar testes

### 🗄️ **Gerenciamento de Dados**
- Request object completo
- Response com múltiplos formatos
- Upload de arquivos
- Manipulação de headers
- Cookies e sessões
- Repositórios para abstração de dados

### 🛡️ **Segurança**
- JWT para APIs
- Validação robusta
- Middleware de segurança
- Rate limiting

## 🚀 **Próximos Passos para Expandir (Atualizados)**

1. **ORM/Database** - Sistema de banco de dados
2. **Template Engine** - Sistema de views avançado
3. **Cache** - Sistema de cache
4. **Queue** - Sistema de filas
5. **Events** - Sistema de eventos
6. **CLI** - Comandos de linha
7. **Testing** - Framework de testes
8. **Documentação automática de rotas**
9. **Job Queue**
10. **Mailing**

## 📝 **Exemplo de Container Simples**

```php
class Container {
    protected $bindings = [];
    public function bind($abstract, $concrete) { $this->bindings[$abstract] = $concrete; }
    public function make($abstract) {
        if (isset($this->bindings[$abstract])) {
            return call_user_func($this->bindings[$abstract]);
        }
        return new $abstract;
    }
}
```

## 📚 **Referências para Inspiração**

- [Laravel Container](https://laravel.com/docs/10.x/container)
- [Symfony EventDispatcher](https://symfony.com/doc/current/components/event_dispatcher.html)
- [PSR-11 Container Interface](https://www.php-fig.org/psr/psr-11/)

# 🚀 **Recursos da Aplicação**

Com essa estrutura, nossa aplicação terá os seguintes recursos:

## 🚀 **Recursos Principais do Framework**

### 🌐 **Sistema de Roteamento Avançado**
- ✅ Suporte a todos os métodos HTTP (GET, POST, PUT, DELETE, PATCH)
- ✅ Rotas com parâmetros dinâmicos (`/user/{id}`, `/post/{slug}`)
- ✅ Grupos de rotas com prefixos (`/admin/*`, `/api/v1/*`)
- ✅ Middleware por rota individual ou grupo
- ✅ Nomes de rotas para geração de URLs
- ✅ Rotas RESTful automáticas (resource routes)

### 🎯 **Sistema MVC Completo**
- ✅ Controllers com classe base rica em funcionalidades
- ✅ Models (a implementar com ORM simples)
- ✅ Views com sistema de templates
- ✅ Injeção automática de dependências

### 🔐 **Autenticação e Autorização**
- ✅ Sistema de autenticação tradicional (sessões)
- ✅ Autenticação JWT para APIs
- ✅ Middleware de autenticação
- ✅ Gerenciamento de sessões seguro
- ✅ Proteção contra ataques comuns

### 🌍 **API RESTful Completa**
- ✅ Endpoints padronizados REST
- ✅ Autenticação JWT
- ✅ Respostas JSON estruturadas
- ✅ Códigos de status HTTP apropriados
- ✅ Tratamento de erros padronizado
- ✅ CORS configurável

### 🛡️ **Sistema de Middleware**
- ✅ Middleware de autenticação
- ✅ Middleware JWT
- ✅ Middleware CORS
- ✅ Rate limiting
- ✅ Middleware customizáveis
- ✅ Pipeline de middleware

### 📝 **Validação de Dados**
- ✅ Validação de formulários
- ✅ Validação de dados JSON
- ✅ Regras de validação customizáveis
- ✅ Mensagens de erro personalizadas
- ✅ Validação automática em controllers

### 🗄️ **Gerenciamento de Dados**
- ✅ Request object completo
- ✅ Response com múltiplos formatos
- ✅ Upload de arquivos
- ✅ Manipulação de headers
- ✅ Cookies e sessões

## 🎯 **Funcionalidades Específicas**

### 📱 **Para Aplicações Web**
```php
// Rotas web com views
$router->get('/', 'HomeController@index');
$router->get('/dashboard', 'DashboardController@index')->middleware('auth');
$router->post('/contact', 'ContactController@send');

// Formulários com validação
public function store() {
    $data = $this->validate([
        'name' => 'required|min:3',
        'email' => 'required|email'
    ]);
    // Processar dados...
}
```

### 🔌 **Para APIs**
```php
// API com JWT
$router->group(['prefix' => 'api', 'middleware' => 'jwt'], function($router) {
    $router->get('/users', 'UserController@index');
    $router->post('/users', 'UserController@store');
});

// Respostas JSON padronizadas
return $this->json([
    'success' => true,
    'data' => $users,
    'message' => 'Users retrieved successfully'
]);
```

### 🔒 **Sistema de Autenticação**
```php
// Login tradicional
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// API com JWT
$router->post('/api/auth/login', 'AuthController@apiLogin');
$router->get('/api/user', 'AuthController@user')->middleware('jwt');
```

## 🛠️ **Recursos Técnicos**

### ⚡ **Performance**
- ✅ Singleton pattern para Application
- ✅ Lazy loading de componentes
- ✅ Cache de rotas (a implementar)
- ✅ Otimização de autoload

### 🔧 **Configuração**
- ✅ Arquivos de configuração centralizados
- ✅ Configuração por ambiente (dev/prod)
- ✅ Variáveis de ambiente
- ✅ Configuração de JWT

### 📊 **Debugging e Logs**
- ✅ Tratamento de exceções
- ✅ Logs de erro (a implementar)
- ✅ Debug mode
- ✅ Stack trace detalhado

### 🔄 **Extensibilidade**
- ✅ Sistema de middleware extensível
- ✅ Controllers customizáveis
- ✅ Helpers globais
- ✅ Service providers (a implementar)

## 📋 **Casos de Uso Práticos**

### 🏢 **Aplicação Empresarial**
- Dashboard administrativo
- CRUD de usuários
- Sistema de permissões
- Relatórios e analytics

### 🛒 **E-commerce**
- Catálogo de produtos
- Carrinho de compras
- Sistema de pagamento
- API para mobile

### 📱 **SPA + API**
- Backend API completo
- Autenticação JWT
- CRUD operations
- Real-time updates

### 🌐 **Multi-tenant**
- Subdomínios por cliente
- Dados isolados
- Configurações por tenant

## 🎯 **Vantagens do Framework**

### ✅ **Simplicidade**
- Estrutura clara e organizada
- Curva de aprendizado baixa
- Documentação completa

### ✅ **Flexibilidade**
- Pode ser web app ou API
- Middleware customizáveis
- Extensível e modular

### ✅ **Segurança**
- JWT para APIs
- Validação robusta
- Middleware de segurança

### ✅ **Produtividade**
- Boilerplate mínimo
- Convenções claras
- Ferramentas integradas

### ✅ **Escalabilidade**
- Arquitetura modular
- Separação de responsabilidades
- Fácil manutenção

## 🚀 **Próximos Passos para Expandir**

1. **ORM/Database** - Sistema de banco de dados
2. **Template Engine** - Sistema de views avançado
3. **Cache** - Sistema de cache
4. **Queue** - Sistema de filas
5. **Events** - Sistema de eventos
6. **CLI** - Comandos de linha
7. **Testing** - Framework de testes

## 🎉 **Conclusão**

Com essa estrutura, você terá um framework completo e funcional para desenvolver tanto aplicações web tradicionais quanto APIs modernas! O framework oferece:

- **Roteamento flexível** com suporte a middleware
- **Sistema MVC** bem estruturado
- **Autenticação** tanto tradicional quanto JWT
- **API RESTful** completa
- **Validação** robusta de dados
- **Extensibilidade** para futuras funcionalidades

O framework é **leve**, **rápido** e **fácil de entender**, perfeito para projetos que precisam de uma base sólida sem a complexidade de frameworks maiores.
```
