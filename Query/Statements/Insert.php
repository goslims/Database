<?php
namespace SLiMS\Database\Query\Statements;

use SLiMS\Database\Query\Builder;

class Insert extends Standart
{
    protected function compile()
    {
        $this->raw = $this->grammar->getPattern('insert');

        $this->sql['table'] = $this->getTableName();

        
        $grammarStyle = strtolower($this->grammar->getName()) . 'InsertStyle';

        $this->$grammarStyle();
        

        $this->builder->setDataToExecute($this->data);
        $this->raw = $this->patternParser($this->raw, $this->sql);
    }

    private function mysqlInsertStyle()
    {
        $columns = [];
        foreach ($this->properties['insert'][0]??[] as $column => $value) {
            $columns[] = $this->setQuote($this->removeQuote($column)) . ' = ?';
            $this->data[] = $value;
        }

        $this->sql['columns'] = implode(',', $columns);
    }

    private function pgsqlInsertStyle()
    {
        $columns = [];
        foreach ($this->properties['insert'][0]??[] as $column => $value) {
            $columns[] = $this->setQuote($this->removeQuote($column));
            $this->data[] = $value;
        }

        $this->sql['columns'] = '(' . implode(',', $columns) . ')';
        $this->sql['values'] = '(' . trim(str_repeat('?,', count($columns)), ',') . ')';
    }

    public function hookAfterStatement(Builder $builder)
    {
        $builder->execute();
        $builder->resetStatement();
        return $builder->getLastId();
    }
}