<?php
namespace SLiMS\Database\Query\Statements;

use SLiMS\Database\Query\Builder;
use SLiMS\Database\Query\Clauses\Where;

class Delete extends Standart
{
    use Where;

    public function __construct()
    {
        parent::__construct(...func_get_args());
        
        foreach ($this->builder->getAttributeBeforeStatement() as $type => $value) {
            if (method_exists($this, $type)) {
                if (is_array($value)) foreach ($value as $seq => $val) $this->$type(...$val);
                else $this->$type(...$value);
            } else {
                $this->sql[$type] = $value;
            }
        }
    }

    protected function compile()
    {
        $this->raw = $this->grammar->getPattern('delete');
        
        $criteria = [];
        foreach ($this->sql['where'] as $seq => $value) {
            $criteria[] = implode(' ', $value);
        }
        $this->sql['where'] = 'where ' . implode(',', $criteria);
        $this->sql['table'] = $this->getTableName();

        $this->builder->setDataToExecute($this->data);
        $this->raw = $this->patternParser($this->raw, $this->sql);
    }

    /**
     * Running statement directly without
     * result method such as get, all, cursor
     *
     * @param Builder $builder
     * @return Result
     */
    public function hookAfterStatement(Builder $builder)
    {
        $process = $builder->isAffected();
        $builder->resetStatement();
        return $process;
    }
}