<?php
namespace SLiMS\Database\Query;

use SLiMS\Database\Connector\Connector;

use SLiMS\Database\Connector\Manager;
use SLiMS\Database\Query\Statements\Standart as StatementStandart;
use SLiMS\Database\Query\Statements\Select;

final class Builder
{
    /**
     * Connection manager
     */
    private static ?Manager $manager = null;

    /**
     * Defaykt connection name
     */
    private string $connection = 'default';

    /**
     * Database driver
     */
    private string $driver = '';

    /**
     * Default query statement if not defined
     */
    private string $defaultStatement = Select::class;

    /**
     * Default method in Select statement
     */
    private array $resultMethod = [
        'get', 'all', 'cursor',
        'count', 'avg', 'max',
        'min', 'sum'
    ];

    /**
     * Statement instance
     */
    private ?StatementStandart $statement = null;

    /**
     * Query result processes
     */
    private ?Result $result = null;

    /**
     * Main table name
     */
    private string $baseTable = '';

    /**
     * Data list to execute
     */
    private array $execute = [];

    /**
     * Store magic method before statement instance
     * created
     */
    private array $attributesBeforeStatement = [];

    public function __construct(string $connection = 'default', ?Manager $manager = null)
    {
        $manager = $manager??self::$manager??Manager::getInstance();

        if ($manager === null) {
            throw new \Exception("Connection manager is not loaded before this object");
        }

        self::$manager = $manager;
        $this->driver = self::$manager->getConnection($this->connection)['driver'];
        $this->result = new Result($this, self::$manager);
        $this->statement = new Select(['select'], $this->driver, $this);
    }

    public function baseTable(string $table): Builder
    {
        $this->baseTable = $table;
        return $this;
    }

    public function setDataToExecute(array $data): void
    {
        $this->execute = $data;
    }

    public function getDataToExecute(): array
    {
        return $this->execute;
    }

    public function getBaseTable(): string
    {
        return $this->baseTable;
    }

    public function resetStatement(): void
    {
        $this->statement = null;
        $this->attributesBeforeStatement = [];
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    public function getAttributeBeforeStatement(): array
    {
        $attributes = $this->attributesBeforeStatement;
        $this->resetAttributeBeforeStatement();
        return $attributes;
    }
    public function resetAttributeBeforeStatement(): void
    {
        $this->attributesBeforeStatement = [];
    }

    /**
     * Convert statement into string
     * with compile process
     *
     * @return string
     */
    private function compile(): string
    {
        return (string)$this->statement;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return void
     */
    private function callDefaultStatementIfNotReady(string $method, array $arguments): void
    {
        if (!$this->statement) {
            $statement = $this->defaultStatement;
            $this->statement = new $statement(array_merge([$method], $arguments), $this->driver, $this);
        }
    }

    /**
     * Resolve some method or magic method
     *
     * @param string $method
     * @param array $arguments
     * @return self|\Statements\Standart|Result;
     */
    private function methodResolver(string $method, array $arguments)
    {
        if ($method === 'connection') {
            $this->connection = $arguments[0]??'default';
            $this->driver = self::$manager->getConnection($this->connection)['driver'];
            $this->result = new Result($this, self::$manager);
            $this->statement = new Select(['select'], $this->driver, $this);
            return $this;
        }

        if ($method === 'table') {
            return $this->baseTable(...$arguments);
        }

        if (class_exists($statement = '\SLiMS\Database\Query\Statements\\' . ucfirst($method))) {
            $this->statement = new $statement(array_merge([$method], $arguments), $this->driver, $this);
            if (method_exists($this->statement, 'hookAfterStatement')) {
                return $this->statement->hookAfterStatement($this);
            }
            return $this->statement;
        } else if (!method_exists($this->result, $method) && !in_array($method, $this->resultMethod)) {
            $this->attributesBeforeStatement[$method][] = $arguments;
            return $this;
        }

        if (!$this->attributesBeforeStatement) {
            $this->callDefaultStatementIfNotReady($method, $arguments);
        }

        if (method_exists($this->result, $method)) {
            return $this->result->$method(...$arguments);
        }

        if (method_exists($this->statement??'', $method)) {
            return $this->statement->$method(...$arguments);
        }

        return $this;
    }

    /**
     * Generate builder to string
     * to get formatted sql syntax
     *
     * @return void
     */
    public function toSql(): string
    {
        $this->callDefaultStatementIfNotReady('select', []);
        return (string)$this;
    }

    public static function __callStatic($method, $arguments): Builder
    {
        $instance = new static;
        $instance->methodResolver($method, $arguments);
        
        return $instance;
    }

    public function __call($method, $arguments)
    {
        return $this->methodResolver($method, $arguments);
    }

    public function __toString(): string
    {
        return $this->compile();
    }
}