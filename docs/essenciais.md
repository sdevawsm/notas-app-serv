# 📚 Documentação dos Arquivos Essenciais do Framework

Este documento lista e descreve os arquivos essenciais para o funcionamento do mini framework PHP, incluindo responsabilidades e agrupamento por domínio.

---

## 🏗️ Núcleo e Ciclo de Vida

### `src/Core/Application.php`
**Responsabilidade:** Núcleo da aplicação, inicializa e gerencia ciclo de vida, carrega configurações, rotas e componentes principais.

### `src/Core/Container.php`
**Responsabilidade:** Container de Injeção de Dependências, resolve e instancia dependências automaticamente.

### `src/Core/ServiceProvider.php`
**Responsabilidade:** Base para provedores de serviço, registra e inicializa serviços no container.

### `src/Core/EventDispatcher.php`
**Responsabilidade:** Gerencia eventos e listeners para acoplamento de funcionalidades.

---

## 🌐 HTTP

### `src/Http/Request.php`
**Responsabilidade:** Encapsula e fornece acesso aos dados da requisição HTTP (GET, POST, headers, arquivos, etc).

### `src/Http/Response.php`
**Responsabilidade:** Gerencia e constrói respostas HTTP (status, headers, conteúdo, JSON, redirecionamento, views).

### `src/Http/Route.php`
**Responsabilidade:** Representa uma rota individual, incluindo método, padrão, handler, middlewares e parâmetros.

### `src/Http/Router.php`
**Responsabilidade:** Gerencia o registro, agrupamento, resolução e despacho de rotas.

### `src/Http/Session.php`
**Responsabilidade:** Gerencia sessões HTTP (start, get, set, flash, destroy, etc).

---

## 🛡️ Middleware

### `src/Http/Middleware/AuthMiddleware.php`
**Responsabilidade:** Garante que o usuário está autenticado (sessão).

### `src/Http/Middleware/JWTMiddleware.php`
**Responsabilidade:** Valida tokens JWT em rotas protegidas de API.

### `src/Http/Middleware/CorsMiddleware.php`
**Responsabilidade:** Configura headers CORS para requisições de API.

### `src/Http/Middleware/RateLimitMiddleware.php`
**Responsabilidade:** Limita o número de requisições por IP.

---

## 🔐 Autenticação e JWT

### `src/Auth/JWTService.php`
**Responsabilidade:** Gera, valida e decodifica tokens JWT.

### `app/Controllers/AuthController.php`
**Responsabilidade:** Controlador para login, logout, registro e endpoints de autenticação (tradicional e JWT).

---

## 🎮 Controllers

### `src/Core/Controller.php`
**Responsabilidade:** Classe base para controllers, fornece helpers para responses, validação, views e middleware.

### `app/Controllers/UserController.php`
**Responsabilidade:** Exemplo de controller de recurso, implementa CRUD de usuários.

---

## 🧩 Serviços e Repositórios

### `app/Services/UserService.php`
**Responsabilidade:** Lógica de negócio relacionada a usuários.

### `app/Repositories/UserRepository.php`
**Responsabilidade:** Abstração de acesso a dados de usuários.

---

## 📢 Eventos

### `app/Events/UserRegistered.php`
**Responsabilidade:** Evento disparado ao registrar um usuário.

---

## ⚙️ Configuração

### `config/app.php`
**Responsabilidade:** Configurações principais da aplicação.

### `.env`
**Responsabilidade:** Variáveis de ambiente (ex: DB, JWT_SECRET, APP_ENV).

---

## 🗺️ Rotas

### `routes/web.php`
**Responsabilidade:** Definição das rotas web (controllers, middlewares, grupos, etc).

### `routes/api.php`
**Responsabilidade:** Definição das rotas de API (incluindo autenticação JWT, CRUD, etc).

---

## 🛠️ Utilitários

### `src/Core/Logger.php`
**Responsabilidade:** Centraliza logs da aplicação.

### `src/Core/Cache/FileCache.php`
**Responsabilidade:** Implementação de cache em arquivos.

---

## 🧪 Testes

### `tests/`
**Responsabilidade:** Testes unitários e de integração para controllers, services, middlewares, etc.

---

> Para detalhes de métodos e propriedades de cada arquivo, consulte a documentação específica de métodos. 