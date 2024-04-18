<?php
namespace SLiMS\Database\Query\Grammars;

class Mysql extends Standart
{
    protected array|string $encapsulateChar = '`';
    
    public function encapsulateColumn(string $charToEncapsulate)
    {
        list(
            $prefix, 
            $suffix
        ) = is_array($this->encapsulateChar) ? 
                $this->encapsulateChar : [$this->encapsulateChar,$this->encapsulateChar];

        if (strpos($charToEncapsulate, '.') !== false) {
            $charToEncapsulate = str_replace('.', "$suffix.$prefix", $charToEncapsulate);
        }

        return $prefix . trim($charToEncapsulate) . $suffix;
    }

    public function getPattern(string $statement) 
    {
        switch ($statement) {
            case 'select':
                $pattern = 'select{select} from{table}{join}{where}{groupby}{orderby}{limit}{offset}';
                break;

            case 'insert':
                $pattern = 'insert {ignore}into {table} set {columns}';
                break;

            case 'update':
                $pattern = 'update {table} set {columns} {where}';
                break;
            
            case 'delete':
                $pattern = 'delete from {table} {where}';
                break;
        }

        return $pattern??'';
    }
}