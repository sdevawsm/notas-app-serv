<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasValidation
{
    /**
     * As regras de validação
     */
    protected array $rules = [];

    /**
     * As mensagens de erro de validação
     */
    protected array $messages = [];

    /**
     * Os atributos personalizados para validação
     */
    protected array $attributes = [];

    /**
     * Valida os atributos do modelo
     */
    public function validate(): bool
    {
        $validator = $this->getValidator();

        if ($validator->fails()) {
            $this->errors = $validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Retorna o validador
     */
    protected function getValidator(): \LadyPHP\Validation\Validator
    {
        return new \LadyPHP\Validation\Validator(
            $this->getAttributes(),
            $this->getRules(),
            $this->getMessages(),
            $this->getAttributes()
        );
    }

    /**
     * Retorna as regras de validação
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Retorna as mensagens de erro de validação
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Retorna os atributos personalizados para validação
     */
    public function getValidationAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Define as regras de validação
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    /**
     * Define as mensagens de erro de validação
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * Define os atributos personalizados para validação
     */
    public function setValidationAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Retorna os erros de validação
     */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * Verifica se o modelo tem erros de validação
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Limpa os erros de validação
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
} 