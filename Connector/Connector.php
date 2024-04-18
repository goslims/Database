<?php
namespace SLiMS\Database\Connector;

final class Connector
{
    private static ?Connector $instance = null;
    private $currentConnection = [];

    public static function bind(string $connectionName, ?Manager $connectionManager = null)
    {
        if (self::$instance === null) self::$instance = new Connector;
        return self::$instance->call($connectionName, $connectionManager);
    }

    public static function getCurrentConnection()
    {
        return self::$instance?->currentConnection??[];
    }

    private function isDriverExistsAndGetIt(string $driverName)
    {
        $exists = class_exists($class = '\SLiMS\Database\Connector\Drivers\\' . ucfirst($driverName));
        return $exists ? $class : false;
    }

    private function call(string $connectionName, ?Manager $connectionManager = null)
    {
        if ($connectionManager || isset($GLOBALS['connection_manager'])) {
            $manager = $connectionManager??$GLOBALS['connection_manager'];
            $connectionDetail = $manager->getConnection($connectionName);

            if ($driverClass = $this->isDriverExistsAndGetIt($connectionDetail['driver'])) {
                // send back existing instance
                if (isset($this->currentConnection[$connectionName])) {
                    return $this->currentConnection[$connectionName]->getPdo();
                }

                // Create new instance
                $driverInstance = new $driverClass($connectionDetail);
                $this->currentConnection[$connectionName] = $driverInstance->connect();
                return $this->currentConnection[$connectionName]->getPdo();
            }

            throw new \Exception("Driver {$driverClass} not found!");;
        }
        
        throw new \Exception("Connection manager is not defined");
    }
}