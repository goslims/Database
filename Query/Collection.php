<?php
namespace SLiMS\Database\Query;

use \Ramsey\Collection\AbstractCollection;

final class Collection extends AbstractCollection implements \JsonSerializable
{
    public function getType(): string
    {
        return Record::class;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}