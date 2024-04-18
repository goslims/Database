<?php
namespace SLiMS\Database\Tests\Commands;

use SLiMS\Cli\Command;
use SLiMS\Database\Connector\Manager;
use SLiMS\Database\Query\Builder;

class BuilderTest extends Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'database:builder';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Database Builder Test';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $database = new Manager;
        $database->setAsGlobal();

        $test = Builder::table('biblio');

        $record = $test->where('gmd_id', 32)->first();

        dd($record);
    }
} 