<?php
namespace SLiMS\Database\Query\Statements;

use SLiMS\Database\Query\Clauses\Where;

class Select extends Standart
{
    use Where;

    /**
     * SQL statement chaining list
     */
    protected array $sql = [
        'select' => ' * '
    ];

    /**
     * Statement processor
     */
    protected array $afterStatement = [
        'join' => \SLiMS\Database\Query\Clauses\Join::class,
    ];

    public function __construct()
    {
        parent::__construct(...func_get_args());

        foreach ($this->afterStatement as $key => $class) {
            if (empty($class)) continue;
            $this->afterStatement[$key] = new $class($this->grammar, $this);
        }

        $this->getAttributeBeforeStatement();
    }

    public function selectRaw(string $rawQuery, array $params = [])
    {
        $this->sql['select'] = ' ' . $this->rawProcessor($rawQuery, $params) . ' ';
    }

    /**
     * Get statement attribute such as
     * where, etc before statement calling
     *
     * @param boolean $byPassLoop
     * @return void
     */
    private function getAttributeBeforeStatement(bool $byPassLoop = false)
    {
        foreach ($this->builder->getAttributeBeforeStatement() as $type => $value) {
            if (method_exists($this, $type) && $byPassLoop === false) {
                if (is_array($value)) foreach ($value as $v) $this->$type(...$v);
                else $this->$type(...$value);
            } else if ($this->isJoin($type)) {
                $this->afterStatement['join']->$type(...$value);
            } else {
                $this->sql[$type] = $value;
            }
        }
    }

    private function aggregate(string $type, string $column)
    {
        $this->getAttributeBeforeStatement(byPassLoop: true);

        if ($column !== '*') {
            $column = $this->columnFormatter([$column]);
        } else {
            if ($type !== 'count') throw new \Exception("Asterisk \"*\" character only for count() function");
        }

        $this->sql['select'] = ' '.$type.'(' . trim($column) . ') as total ';
        return $this->get()->first()->total;
    }

    public function count(string $column = '*')
    {
       return $this->aggregate('count', $column);
    }

    public function avg(string $column)
    {
        return $this->aggregate('avg', $column);
    }

    public function sum(string $column)
    {
        return $this->aggregate('sum', $column);
    }

    public function max(string $column)
    {
        return $this->aggregate('max', $column);
    }

    public function min(string $column)
    {
        return $this->aggregate('min', $column);
    }

    public function setSql(string $type, array|string $values)
    {
        $this->sql[$type] = $values;
    }

    public function first()
    {
        return $this->builder->get()->first();
    }

    public function last()
    {
        return $this->builder->get()->last();
    }

    public function all()
    {
        return $this->builder->cursor();
    }

    public function groupBy()
    {
        $this->sql['groupby'] = 'group by ' . implode(',', array_map(fn($col) => $this->setQuote($col), func_get_args()));
        return $this;
    }

    public function orderBy()
    {
        $orderBy = func_get_args();

        if (!isset($this->sql['orderby'] )) $this->sql['orderby'] = '';

        $this->sql['orderby'] .= 'order by ';
        if (count($orderBy) == 1) {
            $this->sql['orderby'] .= implode(',', array_map(fn($item) => $this->setQuote($item[0]) . ' ' . $this->removeQuote($item[1]), $orderBy[0])) . ' ';
        } else {
            $this->sql['orderby'] .= $this->setQuote($orderBy[0]) . ' ' . $this->removeQuote($orderBy[1]) . ' ';
        }

        return $this;
    }

    public function limit(int $limit)
    {
        $this->sql['limit'] = 'limit ' . ((int)$limit) . ' ';

        return $this;
    }

    public function offset(int $offset)
    {
        $this->sql['offset'] = 'offset ' . ((int)$offset);

        return $this;
    }

    protected function compile()
    {
        $this->getAttributeBeforeStatement();
        $this->builder->setDataToExecute($this->data);

        $this->raw = $this->grammar->getPattern('select');

        if (isset($this->properties['select']) && $this->properties['select']) {
            $this->sql['select'] = $this->columnFormatter($this->properties['select']);
        }

        $this->sql['table'] = '' . $this->getTableName();

        if (isset($this->sql['join'])) {
            $this->sql['join'] = $this->afterStatement['join']->compile();
        }

        if (isset($this->sql['where'])) {
            $this->sql['where'] = 'where ' . implode(' and ', array_map(function($item) {
                return implode(' ', $item);
            }, $this->sql['where']));
        }

        $this->raw = $this->patternParser($this->raw, $this->sql);
    }

    public function isJoin(string $method)
    {
        return preg_match('/join/i', $method);
    }

    public function __call($method, $arguments)
    {
        if ($this->isJoin($method)) {
            $afterStatementMethod = strtolower(str_replace(['left','right','inner'], '', $method));
            $part = $this->afterStatement[$afterStatementMethod];
            return $part->$method(...$arguments);
        }

        // Send back to builder
        if (method_exists($this->builder, $method) || method_exists($this, '__call')) {
            return $this->builder->$method(...$arguments);
        }

        // or register method as sql
        $this->sql[$afterStatementMethod] = $arguments;
        return $this;
    }
}