<?php
namespace SLiMS\Database\Query\Clauses;

trait Where
{
    private int $whereHit = 0;
    
    public function where()
    {
        $this->whereHit++;

        $args = func_get_args();
        if (!isset($this->sql['where'])) $this->sql['where'] = [];

        if (func_num_args() === 3) {
            $args[0] = $this->setQuote($args[0]);

            if ($args[2]) {
                $this->data = array_merge($this->data, (is_array($args[2]) ? $args[2] : [$args[2]]));
                $args[2] = is_array($args[2]) ? '(' . trim(str_repeat('?,', count($args[2])), ',') . ')' : '?';
            } else {
                unset($args[2]);
            }
            
            $this->sql['where'][] = $args;
        } else if (func_num_args() === 2) {
            $this->sql['where'][] = [$this->setQuote($args[0]), '=', '?'];
            $this->data[] = $args[1];
        } else {
            throw new \Exception("Where clauses must have at least 2 arguments");
        }
        
        return $this;
    }

    public function whereNull(string $column)
    {
        return $this->where($column, 'is null', null);
    }

    public function whereNotNull(string $column)
    {
        return $this->where($column, 'is not null', null);
    }

    public function whereIn(string $column, array $values)
    {
        return $this->where($column, 'in', $values);
    }

    public function whereNotIn(string $column, array $values)
    {
        return $this->where($column, 'not in', $values);
    }

    public function like(string $column, string $value, string $type = 'both')
    {
        if ($type === 'both') {
            $value = '%' . $value . '%';
        } else if ($type === 'first') {
            $value = $value . '%';
        } else if ($type === 'end') {
            $value = '%' . $value;
        }

        return $this->where($column, 'like', $value);
    }
}
