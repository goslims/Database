<?php
namespace SLiMS\Database\Query;

use PDO;
use PDOException;
use PDOStatement;
use Generator;
use SLiMS\Database\Connector\Connector;

class Result
{
    private ?PDO $conn = null;
    private ?Builder $builder = null;

    public function __construct(Builder $builder, $manager)
    {
        $this->builder = $builder;
        $this->conn = Connector::bind($this->builder->getConnection(), $manager);
    }

    /**
     * Get query is success or not
     *
     * @param array $options
     * @return boolean
     */
    public function isAffected(array $options = []): bool
    {
        $statement = $this->execute($options);
        return (bool)$statement->rowCount();
    }

    /**
     * Get last increment id
     *
     * @return int
     */
    public function getLastId(): int
    {
        return (int)$this->conn->lastInsertId();
    }

    /**
     * Execute formatted query
     *
     * @param array $options
     * @return PDOStatement
     */
    public function execute(array $options = []): PDOStatement
    {
        $statement = $this->conn->prepare($this->builder->toSql(), $options);
        $statement->execute($this->builder->getDataToExecute());

        return $statement;
    }

    /**
     * Retrieve all data into 
     * Record Collection
     *
     * @return 
     */
    public function get(): Collection
    {
        $statement = $this->execute();
        $collection = new Collection;
        while ($result = $statement->fetchObject(Record::class)) {
            $collection->add($result);
        }

        return $collection;
    }

    /**
     * Get first record
     *
     * @return Record
     */
    public function first()
    {
        return $this->get()->first();
    }

    /**
     * Get last record
     *
     * @return Record
     */
    public function last()
    {
        return $this->get()->last();
    }

    /**
     * Retrieve data from databse
     * with cursor strategy
     *
     * @return \generator
     */
    public function cursor(): \generator
    {
        $statement = $this->execute([PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);

        $currentRecord = 0;
        while ($currentRecord < $statement->rowCount()) {
            $currentRecord++;
            yield $statement->fetchObject(Record::class);
        }
    }

    /**
     * Printout some query
     *
     * @return void
     */
    public function debug()
    {
        dd($this->builder->toSql(), $this->builder->getDataToExecute());
    }
}