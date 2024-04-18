<?php
namespace SLiMS\Database\Connector\Drivers;

abstract class Contract
{
    protected string $name = '';
    protected string $dsn = '';
    protected string $username = '';
    protected string $password = '';
    protected array $options = [];
    protected ?\PDO $pdo = null;

    public function __construct(array $detail)
    {
        unset($detail['options']['storage_engine']);
        $this->dsnParser($detail);
        $this->username = $detail['username'];
        $this->password = $detail['password'];
        $this->options = $detail['options'];
    }

    public function connect()
    {
        $this->pdo = new \PDO($this->dsn, $this->username, $this->password);
        foreach ($this->options??[] as $option) {
            $this->setAttribute($option[0], $option[1]);
        }

        return $this;
    }

    public function getPdo() {
        return $this->pdo;
    }

    abstract protected function dsnParser(array $detail);
}