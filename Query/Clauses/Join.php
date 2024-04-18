<?php
namespace SLiMS\Database\Query\Clauses;
use SLiMS\Database\Query\Utility;

class Join
{
    use Utility;

    private ?object $statement = null;
    private ?object $grammar = null;
    private array $data = [];

    public function __construct(object $grammar, object $statement)
    {
        $this->grammar = $grammar;
        $this->statement = $statement;
    }

    public function getData()
    {
        return $this->data;
    }

    public function compile()
    {
        $sql = [];
        foreach ($this->data as $table => $attributes) {
            $table = $this->aliasExtractor($table);
            if (is_array($table)) {
                $table = implode(' as ', array_map(fn($item) => $this->setQuote($item), $table));
            }
            list($type, $firstCol, $operator, $sencondCol) = $attributes;

            $sql[] = (($type !== 'join') ? $type . ' join' : 'join') . 
                     ' ' . $table . ' on ' . 
                     $this->setQuote($firstCol) . 
                     ' ' . $operator . ' ' .
                     $this->setQuote($sencondCol);
        }

        return trim(implode(' ', $sql));
    }

    public function __call($method, $arguments)
    {
        $this->statement->setSql('join', '');

        $type = str_replace('join', '', strtolower($method));

        if (($totalArgument = count($arguments)) < 4) {
            if (is_array($arguments[0])) {
                foreach ($arguments as $join) 
                    $this->data[$join[0]] = array_merge([$type], array_slice($join, 1));

                return $this->statement;
            } else {
                throw new \Exception("Join clause must be have 4 argument, {$totalArgument} given");
            }
        }

        if (empty($type)) $type = 'join';

        $this->data[$arguments[0]] = array_merge([$type], array_slice($arguments, 1));

        return $this->statement;
    }
}