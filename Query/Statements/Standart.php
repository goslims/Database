<?php
namespace SLiMS\Database\Query\Statements;

use SLiMS\Database\Query\Builder;
use SLiMS\Database\Query\Grammars\Standart as GrammarStandart;
use SLiMS\Database\Query\Utility;

abstract class Standart
{
    use Utility;
    
    protected string $name = '';
    protected ?Builder $builder = null;
    protected array $properties = [];
    protected array $afterStatement = [];
    protected array $data = [];
    protected array $sql = [];
    protected string $raw = '';
    protected ?GrammarStandart $grammar = null;

    public function __construct(array $properties, string $grammar, Builder $builder)
    {
        $this->properties[$properties[0]] = array_slice($properties, 1); // first index as method name;
        $grammarClass = '\SLiMS\Database\Query\Grammars\\' . ucfirst($grammar);
        $this->grammar = new $grammarClass;
        $this->builder = $builder;

        if (empty($this->name)) $this->name = strtolower((new \ReflectionClass($this))->getShortName());
    }

    public function getName()
    {
        return $this->name;
    }

    abstract protected function compile();

    public function __toString()
    {
        $this->compile();
        return $this->raw;
    }

    public function __destruct()
    {
        $this->properties = [];
        $this->sql = [];
        $this->grammar = null;
    }
}