<?php

namespace LadyPHP\Validation;

class Validator
{
    protected array $data = [];
    protected array $rules = [];
    protected array $messages = [];
    protected array $errors = [];
    protected array $customRules = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->registerDefaultRules();
    }

    protected function registerDefaultRules(): void
    {
        $this->customRules = [
            'required' => [$this, 'validateRequired'],
            'email' => [$this, 'validateEmail'],
            'min' => [$this, 'validateMin'],
            'max' => [$this, 'validateMax'],
            'numeric' => [$this, 'validateNumeric'],
            'string' => [$this, 'validateString'],
            'array' => [$this, 'validateArray'],
            'url' => [$this, 'validateUrl'],
        ];
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($rules as $rule) {
                $parameters = [];
                
                if (str_contains($rule, ':')) {
                    [$rule, $parameter] = explode(':', $rule, 2);
                    $parameters = explode(',', $parameter);
                }

                $value = $this->getValue($field);
                
                if (!$this->validateRule($field, $rule, $value, $parameters)) {
                    $this->addError($field, $rule, $parameters);
                }
            }
        }

        return empty($this->errors);
    }

    protected function validateRule(string $field, string $rule, $value, array $parameters = []): bool
    {
        if (!isset($this->customRules[$rule])) {
            throw new \InvalidArgumentException("Validation rule '{$rule}' does not exist.");
        }

        return call_user_func($this->customRules[$rule], $field, $value, $parameters);
    }

    protected function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }

    protected function addError(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->messages[$field . '.' . $rule] ?? $this->getDefaultMessage($field, $rule, $parameters);
        $this->errors[$field][] = $message;
    }

    protected function getDefaultMessage(string $field, string $rule, array $parameters = []): string
    {
        $messages = [
            'required' => 'The :field field is required.',
            'email' => 'The :field must be a valid email address.',
            'min' => 'The :field must be at least :min characters.',
            'max' => 'The :field may not be greater than :max characters.',
            'numeric' => 'The :field must be a number.',
            'string' => 'The :field must be a string.',
            'array' => 'The :field must be an array.',
            'url' => 'The :field must be a valid URL.',
        ];

        $message = $messages[$rule] ?? 'The :field field is invalid.';
        $message = str_replace(':field', $field, $message);

        foreach ($parameters as $key => $value) {
            $message = str_replace(':' . $key, $value, $message);
        }

        return $message;
    }

    // Validation Rules
    protected function validateRequired(string $field, $value): bool
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        }
        return true;
    }

    protected function validateEmail(string $field, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMin(string $field, $value, array $parameters): bool
    {
        $min = $parameters[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }
        
        if (is_array($value)) {
            return count($value) >= $min;
        }
        
        return false;
    }

    protected function validateMax(string $field, $value, array $parameters): bool
    {
        $max = $parameters[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        
        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }
        
        if (is_array($value)) {
            return count($value) <= $max;
        }
        
        return false;
    }

    protected function validateNumeric(string $field, $value): bool
    {
        return is_numeric($value);
    }

    protected function validateString(string $field, $value): bool
    {
        return is_string($value);
    }

    protected function validateArray(string $field, $value): bool
    {
        return is_array($value);
    }

    protected function validateUrl(string $field, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function addCustomRule(string $name, callable $rule): void
    {
        $this->customRules[$name] = $rule;
    }
} 