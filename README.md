


Context
— none fetched
Mini Framework PHP
Este é um mini framework PHP desenvolvido para fornecer uma estrutura simples, leve e eficiente para o desenvolvimento de aplicações web.

Estrutura de Diretórios
/mini-framework/
├── app/                    # Código da aplicação
│   ├── Controllers/        # Controladores que gerenciam as requisições
│   ├── Models/             # Modelos para interação com o banco de dados
│   ├── Views/              # Templates e arquivos de visualização
│   ├── Middlewares/        # Middlewares para processamento de requisições
│   └── Services/           # Serviços da aplicação e lógica de negócios
├── config/                 # Arquivos de configuração
│   ├── app.php             # Configurações gerais da aplicação
│   ├── database.php        # Configurações de banco de dados
│   └── routes.php          # Configurações de rotas (alternativo)
├── public/                 # Ponto de entrada público
│   ├── index.php           # Front controller - ponto de entrada único
│   ├── .htaccess           # Configuração para redirecionamento de URLs
│   ├── assets/             # Assets estáticos
│   │   ├── css/            # Arquivos CSS
│   │   ├── js/             # Arquivos JavaScript
│   │   └── images/         # Imagens
├── routes/                 # Definições de rotas
│   ├── web.php             # Rotas para interface web
│   └── api.php             # Rotas para API (se aplicável)
├── src/                    # Código do framework
│   ├── Core/               # Classes principais do framework
│   │   └── Application.php # Classe principal da aplicação
│   ├── Database/           # Classes de conexão com banco de dados
│   │   ├── Connection.php  # Gerenciamento de conexões
│   │   └── QueryBuilder.php# Construtor de consultas
│   ├── Http/               # Classes relacionadas a requisições HTTP
│   │   ├── Router.php      # Sistema de roteamento
│   │   ├── Request.php     # Manipulação de requisições HTTP
│   │   ├── Response.php    # Manipulação de respostas
│   │   ├── Session.php     # Gerenciamento de sessões
│   │   └── Middleware/     # Middlewares para processamento de requisições
│   └── Helpers/            # Funções auxiliares
│       └── functions.php   # Funções globais úteis
├── storage/                # Armazenamento de arquivos
│   ├── logs/               # Logs da aplicação
│   ├── cache/              # Arquivos de cache
│   └── uploads/            # Uploads de usuários
├── tests/                  # Testes automatizados
│   ├── Unit/               # Testes unitários
│   └── Feature/            # Testes de funcionalidades
├── vendor/                 # Dependências (gerenciadas pelo Composer)
├── .env                    # Variáveis de ambiente
├── .env.example            # Exemplo de variáveis de ambiente
├── .gitignore              # Arquivos ignorados pelo Git
├── composer.json           # Configuração do Composer
└── README.md               # Documentação