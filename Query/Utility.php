<?php
namespace SLiMS\Database\QUery;

trait Utility
{
    /**
     * Determine and extract alias character
     *
     * @param string $char
     * @return array|string
     */
    public function aliasExtractor(string $char): array|string
    {
        preg_match('/(\sAS\s)/i', $char, $match);
        
        $char = $this->removeQuote($char);
        return $match ? explode($match[0]??'', $char) : $char;
    }

    /**
     * setup column encloser based on
     * database driver
     *
     * @param string $input
     * @return string
     */
    public function setQuote(string $input): string
    {
        return $this->grammar->encapsulateColumn(trim($input));
    }

    /**
     * From quote from user input
     *
     * @param string $char
     * @return string
     */
    public function removeQuote(string $char): string
    {
        return str_replace(['\'','"','`'], '', $char);
    }

    /**
     * Parse grammar statement pattern
     *
     * @param string $pattern
     * @param array $data
     * @return string
     */
    public function patternParser(string $pattern, array $data): string
    {
        foreach ($this->sql as $key => $value) {
            if (is_array($value)) continue;
            $value = trim($value);
            $pattern = str_replace('{'.$key.'}', ' ' . $value, $pattern);
        }

        // Remove unused pattern
        return trim(preg_replace('/{(.*?)}/', trim(''), $pattern));
    }

    /**
     * set column string as grammar standart
     *
     * @param array $property
     * @return string
     */    
    public function columnFormatter(array $property): string
    {
        return ' ' . implode(',', array_map(function($column) {
            $column = $this->aliasExtractor($column);
            if (is_string($column)) return $this->setQuote($column);

            return implode(' as ', array_map(fn($col) => $this->setQuote($col), $column));
        }, $property)) . ' ';
    }

    public function getTableName(): string
    {
        $table = $this->aliasExtractor($this->builder->getBaseTable());
        if (is_string($table)) $table = $this->setQuote($table);
        else $table = implode(' as ', array_map(fn($item) => $this->setQuote($item), $table));

        return $table;
    }

    /**
     * Give prefix and suffix based on
     * grammar encloser character
     *
     * @param string $input
     * @return int|string
     */
    public function encapsulateByDataType(string $input): int|string
    {
        if (gettype($input) == 'string') {
            if (preg_match('/^[0-9]*$/', $input)) return (int)$input;
            return $this->setQuote($input);
        }

        return (int)$input;
    }

    /**
     * Parsing raw query into simple
     * query
     *
     * @param string $rawQuery
     * @param array $params
     * @return string
     */
    public function rawProcessor(string $rawQuery, array $params): string
    {
        if (preg_match('/\?/', $rawQuery) && $params) {
            $indexMatch = 0;
            $split = str_split($rawQuery);
            foreach ($split as $index => $char) {
                if (trim($char) === '?') {
                    $split[$index] = $this->encapsulateByDataType($params[$indexMatch]);
                    $indexMatch++;
                }
            }
            $rawQuery = implode('', $split);
        }

        return $rawQuery;
    }
}