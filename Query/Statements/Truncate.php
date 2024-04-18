<?php
namespace SLiMS\Database\Query\Statements;

use SLiMS\Database\Query\Builder;

class Truncate extends Standart
{
    protected function compile()
    {
        $this->raw = 'TRUNCATE TABLE ' . $this->getTableName();
    }

    public function hookAfterStatement(Builder $builder)
    {
        $builder->execute();
        $builder->resetStatement();
    }
}