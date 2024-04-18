<?php
namespace SLiMS\Database\Connector\Drivers;

final class Mysql extends Contract
{
    protected string $name = 'Mysql';
    protected string $dsn = 'mysql:host={host};dbname={database};port={port}';

    protected function dsnParser(array $detail)
    {
        foreach ($detail as $key => $value) {
            if (is_array($value)) continue;
            $this->dsn = str_replace('{' . $key . '}', $value, $this->dsn);
        }
    }
}