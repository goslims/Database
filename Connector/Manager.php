<?php
namespace SLiMS\Database\Connector;

use SLiMS\DB;

final class Manager
{
    /**
     * Connection list
     */
    private array $connections = [];

    /**
     * Instance as global access
     */
    private static ?Manager $instance = null;

    /**
     * Database components
     */
    private array $components = [
        \SLiMS\Database\Query\Builder::class => null
    ];

    public function __construct() {
        // if in SLiMS 9 environment
        if (($database = config('database')) !== null) {
            $default = $database['default_profile']??'';
            foreach ($database['nodes'] as $name => $detail) {
                if (!isset($detail['driver'])) $detail['driver'] = 'mysql';
                if ($name === $default) {
                    $this->addConnection($detail);
                    continue;
                }

                $this->addConnection($detail, $name);
            }
        }

        $this->loadComponents();
    }

    private function loadComponents()
    {
        foreach ($this->components as $component => $instance) {
            $this->components[$component] = new $component(manager: $this);
        }
    }

    public function addConnection(array $detail, string $name = 'default') 
    {
        $this->connections[$name] = $detail;
    }

    public function setAsGlobal()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function getConnection(string $name = 'default')
    {
        return $this->connections[$name]??null;
    }

    public function getComponents()
    {
        return $this->components;
    }

    public static function __callStatic($method, $arguments) 
    {
        $manager = self::getInstance();

        if ($manager === null) 
            throw new \Exception("Cannot call manager as global");
        
        foreach ($manager->getComponents() as $component => $instance) {
            if ($instance && method_exists($instance, $method)) {
                return call_user_func_array([$instance, $method], $arguments);
            } else {
                return $instance->$method(...$arguments);
            }
        }
    }
}