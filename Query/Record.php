<?php
namespace SLiMS\Database\Query;

class Record implements \JsonSerializable
{
    private array $original = [];
    private array $attributes = [];

    public function __set($key, $value) 
    {
        $this->original[$key] = $value;
        $this->attributes[$key] = $value;
    }

    public function __get($key)
    {
        return $this->attributes[$key]??null;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }
}