<?php

class Autoloader
{
    protected $namespacesMap = [];

    public function addNamespace(string $namespace, string $rootDir): bool
    {
        if (is_dir($rootDir)) {
            $this->namespacesMap[$namespace] = $rootDir;
            return true;
        }

        return false;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'autoload']);
    }

    protected function autoload($class): bool
    {
        $pathParts = explode('\\', $class);
        if (is_array($pathParts)) {
            $namespace = array_shift($pathParts);
            if (!empty($this->namespacesMap[$namespace])) {
                require_once $this->namespacesMap[$namespace] . '/' . implode('/', $pathParts) . '.php';
                return true;
            }
        }

        return false;
    }

}