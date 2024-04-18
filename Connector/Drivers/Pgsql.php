<?php
namespace SLiMS\Database\Connector\Drivers;

final class Pgsql extends Contract
{
    protected string $name = 'Pgsql';
    protected string $dsn = 'pgsql:host={host};dbname={database};port={port}';

    protected function dsnParser(array $detail)
    {
        foreach ($detail as $key => $value) {
            if (is_array($value)) continue;
            $this->dsn = str_replace('{' . $key . '}', $value, $this->dsn);
        }
    }
}