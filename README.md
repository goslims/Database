# SLiMS Database
A small SLiMS component to working with popular relational database management system (RDBMS) such as MySQL, MariaDB and PostgreSQL.

## ⚠️ WARNING
This repo only supports SLiMS 9 Bulian and Next

## How to
```php
<?php
use SLiMS\Database\Connector\Manager;
use SLiMS\Database\Query\Builder;

$manager = new Manager;
$manager->setAsGlobal();

// using query builder
$test = Builder::table('biblio');

// Get all record from biblio table
$record = $test->get();

// Get a record with where criteria
$records = $test->where('biblio_id', 1)->get();

// Get some record from biblio
foreach($records as $record) {
    echo $record->title;
}

// get record with some column
$record = $test->select('title', 'publisher_name')->where('biblio_id', 1)->get();

echo $record[0]->title;

// get first record
$record = $test->select('title', 'publisher_name')->where('gmd_id', 32)->first();

echo $record->title;
```
