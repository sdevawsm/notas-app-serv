<?php

namespace LadyPHP\Validation;

class ValidatorFactory
{
    public static function make(array $data, array $rules, array $messages = []): Validator
    {
        return new Validator($data, $rules, $messages);
    }

    public static function validate(array $data, array $rules, array $messages = []): array
    {
        $validator = static::make($data, $rules, $messages);
        
        if (!$validator->validate()) {
            return [
                'success' => false,
                'errors' => $validator->getErrors()
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }
} 