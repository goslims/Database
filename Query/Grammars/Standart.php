<?php
namespace SLiMS\Database\Query\Grammars;

abstract class Standart
{
    protected array|string $encapsulateChar = '';
    protected array $dataTypes = [];
    protected array $operator = [
        'arithmetic' => ['+','-','*','/','%'],
        'bitwise' => ['&','|','^'],
        'comparison' => ['=','>','<','>=','<=','<>'],
        'compound' => ['+=','-=','*=','/=','%=','&=','^-=','|*='],
        'logical' => ['ALL','AND','ANY','BETWEEN','EXISTS','IN','LIKE','NOT','OR','SOME']
    ];

    public function getName()
    {
        $class = new \ReflectionClass($this);
        return $class->getShortName();
    }

    public function getEncapsulateChar()
    {
        return $this->encapsulateChar;
    }

    public function getOperator(string $name = 'all')
    {
        return $this->operator[$name]??$this->operator;
    }

    public function isValidOperator(string $char) {
        $valid = false;
        foreach ($this->getOperator() as $type => $operators) {
            if (in_array(strtoupper($char), $operators)) {
                $valid = true;
                break;
            }
        }

        return $valid;
    }

    abstract protected function encapsulateColumn(string $charToEncapsulate);
    abstract protected function getPattern(string $template);
}