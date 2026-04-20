<?php

declare(strict_types=1);

namespace App\Core;

class Container
{
    // Stocke les "recettes" pour créer chaque classe
    private array $bindings = [];

    // Stocke les instances déjà créées (singletons)
    private array $instances = [];

    /**
     * Enregistrer une recette de création
     */
    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Enregistrer un singleton
     * La même instance est retournée à chaque appel
     */
    public function singleton(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = function() use ($abstract, $factory) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $factory($this);
            }
            return $this->instances[$abstract];
        };
    }

    /**
     * Créer ou récupérer une instance
     */
    public function make(string $abstract): object
    {
        // Si une recette existe → l'utiliser
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        // Sinon → essayer de créer automatiquement
        return $this->autoResolve($abstract);
    }

    /**
     * Créer automatiquement une classe
     * en analysant son constructeur
     */
    private function autoResolve(string $class): object
    {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(
                "Impossible de créer la classe {$class} : " . $e->getMessage()
            );
        }

        $constructor = $reflection->getConstructor();

        // Pas de constructeur → créer directement
        if ($constructor === null) {
            return new $class();
        }

        $parameters = $constructor->getParameters();

        // Pas de paramètres → créer directement
        if (empty($parameters)) {
            return new $class();
        }

        // Résoudre chaque paramètre du constructeur
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                throw new \RuntimeException(
                    "Impossible de résoudre le paramètre 
                     {$parameter->getName()} de {$class}"
                );
            }

            $dependencies[] = $this->make($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}