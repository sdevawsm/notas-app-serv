# Guia de Implementação do Banco de Dados - LadyPHP Framework

## 1. Visão Geral

Este documento descreve a implementação do sistema de banco de dados do LadyPHP Framework, inspirado no Eloquent ORM do Laravel, mas com uma abordagem mais leve e personalizada.

## 2. Estrutura de Diretórios

```
src/Database/
├── Connection/
│   ├── ConnectionManager.php      # Gerenciador de conexões
│   ├── ConnectionInterface.php    # Interface para conexões
│   └── Drivers/
│       ├── MySQLConnection.php    # Driver MySQL
│       └── PostgreSQLConnection.php # Driver PostgreSQL
├── ORM/
│   ├── Model.php                  # Classe base do Model
│   ├── QueryBuilder.php           # Construtor de queries
│   ├── Relations/
│   │   ├── RelationInterface.php  # Interface para relacionamentos
│   │   ├── HasOne.php            # Relacionamento 1:1
│   │   ├── HasMany.php           # Relacionamento 1:N
│   │   ├── BelongsTo.php         # Relacionamento N:1
│   │   └── BelongsToMany.php     # Relacionamento N:N
│   ├── Events/
│   │   ├── EventDispatcher.php    # Dispatcher de eventos
│   │   └── ModelEvents.php        # Eventos do modelo
│   └── Cache/
│       ├── QueryCache.php         # Cache de queries
│       └── RelationCache.php      # Cache de relacionamentos
└── Migration/
    ├── Migration.php              # Classe base de migrações
    ├── Schema/
    │   ├── Blueprint.php          # Builder de schema
    │   └── SchemaBuilder.php      # Construtor de schema
    └── Commands/
        ├── MigrateCommand.php     # Comando de migração
        └── RollbackCommand.php    # Comando de rollback
```

## 3. Componentes Principais

### 3.1. Gerenciamento de Conexão

O `ConnectionManager` é responsável por:
- Gerenciar múltiplas conexões
- Implementar pool de conexões
- Suportar diferentes drivers de banco de dados
- Gerenciar transações

```php
interface ConnectionInterface {
    public function connect(): PDO;
    public function disconnect(): void;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
}
```

### 3.2. Model Base

O `Model` base implementa:
- CRUD básico
- Relacionamentos
- Eventos
- Validação
- Timestamps
- Soft deletes

```php
abstract class Model {
    use HasRelations;
    use HasEvents;
    use HasValidation;
    use HasTimestamps;
    use SoftDeletes;
}
```

### 3.3. Sistema de Relacionamentos

Implementação de relacionamentos:
- HasOne (1:1)
- HasMany (1:N)
- BelongsTo (N:1)
- BelongsToMany (N:N)
- Relacionamentos polimórficos

```php
interface RelationInterface {
    public function getResults();
    public function initRelation(array $models);
    public function getEager();
}
```

### 3.4. Query Builder

O `QueryBuilder` oferece:
- Interface fluente
- Construção de queries complexas
- Suporte a joins
- Agregações
- Subqueries
- Paginação

## 4. Boas Práticas

### 4.1. Princípios SOLID

1. **Single Responsibility**
   - Cada classe tem uma única responsabilidade
   - Separação clara entre Model, QueryBuilder e Relations

2. **Open/Closed**
   - Uso de interfaces para extensibilidade
   - Facilidade para adicionar novos drivers

3. **Interface Segregation**
   - Interfaces específicas para cada funcionalidade
   - Evitar interfaces muito grandes

4. **Dependency Inversion**
   - Injeção de dependências
   - Uso de interfaces ao invés de implementações concretas

### 4.2. Padrões de Design

1. **Repository Pattern**
   - Abstração de acesso a dados
   - Separação de lógica de negócio

2. **Unit of Work**
   - Gerenciamento de transações
   - Rastreamento de mudanças

3. **Factory Pattern**
   - Criação de modelos
   - Inicialização de relacionamentos

4. **Observer Pattern**
   - Sistema de eventos
   - Hooks para modelos

### 4.3. Performance

1. **Lazy Loading**
   - Carregamento sob demanda de relacionamentos
   - Otimização de memória

2. **Cache**
   - Cache de queries frequentes
   - Cache de relacionamentos
   - Cache de metadados

3. **Otimizações**
   - Prevenção de queries N+1
   - Batch operations
   - Chunking de resultados

## 5. Validação e Segurança

1. **Validação**
   - Regras de validação nos modelos
   - Validação automática antes de salvar
   - Mensagens de erro personalizadas

2. **Segurança**
   - Prepared statements
   - Escape de dados
   - Proteção contra SQL injection

## 6. Manutenção

### 6.1. Logging

- Log de queries
- Log de erros
- Log de performance

### 6.2. Debugging

- Modo debug
- Profiling de queries
- Stack traces

### 6.3. Documentação

- PHPDoc completo
- Exemplos de uso
- Guias de troubleshooting

## 7. Testes

1. **Unit Tests**
   - Testes de modelos
   - Testes de relacionamentos
   - Testes de queries

2. **Integration Tests**
   - Testes de conexão
   - Testes de transações
   - Testes de cache

3. **Performance Tests**
   - Testes de carga
   - Testes de concorrência
   - Benchmarks

## 8. Exemplos de Uso

### 8.1. Modelo Básico

```php
class User extends Model
{
    protected $fillable = ['name', 'email'];
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

### 8.2. Queries

```php
// Busca básica
$users = User::where('active', true)
    ->orderBy('name')
    ->limit(10)
    ->get();

// Relacionamentos
$user = User::with('posts')->find(1);

// Transações
DB::transaction(function() {
    // Operações
});
```

### 8.3. Migrações

```php
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }
}
```

## 9. Roadmap

1. **Fase 1: Base**
   - Implementação do Model base
   - Query Builder básico
   - Conexão PDO

2. **Fase 2: Relacionamentos**
   - Implementação de relacionamentos
   - Eager loading
   - Lazy loading

3. **Fase 3: Cache e Performance**
   - Sistema de cache
   - Otimizações
   - Profiling

4. **Fase 4: Eventos e Observers**
   - Sistema de eventos
   - Observers
   - Hooks

5. **Fase 5: Validação e Segurança**
   - Sistema de validação
   - Segurança
   - Sanitização

## 10. Contribuição

Para contribuir com o desenvolvimento:

1. Siga os padrões de código
2. Escreva testes
3. Documente alterações
4. Faça pull requests

## 11. Suporte

Para suporte e dúvidas:
- Issues no GitHub
- Documentação
- Exemplos
- Comunidade 