<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function required(string $value, string $field): self
    {
        if (trim($value) === '') {
            $this->errors[$field] = "Le champ {$field} est obligatoire";
        }
        return $this;
    }

    public function email(string $value, string $field = 'email'): self
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse email est invalide";
        }
        return $this;
    }

    public function minLength(string $value, int $min, string $field): self
    {
        if (strlen($value) < $min) {
            $this->errors[$field] = "Le champ {$field} doit contenir au moins {$min} caractères";
        }
        return $this;
    }

    public function maxLength(string $value, int $max, string $field): self
    {
        if (strlen($value) > $max) {
            $this->errors[$field] = "Le champ {$field} ne doit pas dépasser {$max} caractères";
        }
        return $this;
    }

    public function matches(string $value, string $other, string $field): self
    {
        if ($value !== $other) {
            $this->errors[$field] = "Les mots de passe ne correspondent pas";
        }
        return $this;
    }

    public function phone(string $value, string $field = 'téléphone'): self
    {
        // Téléphone algérien : 10 chiffres, commence par 05, 06 ou 07
        $clean = preg_replace('/\s+/', '', $value);
        if (!preg_match('/^0[567]\d{8}$/', $clean)) {
            $this->errors[$field] = "Le numéro de téléphone est invalide (ex: 0612345678)";
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getFirstError(): string
    {
        return array_values($this->errors)[0] ?? '';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}