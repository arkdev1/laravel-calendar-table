<?php

namespace TomShaw\CalendarTable\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CommandTest extends TestCase
{
    protected ?string $tableName;

    protected ?string $consoleCommand;

    protected int $startYear;

    protected int $endYear;

    public function setup(): void
    {
        parent::setUp();

        $this->tableName = config('calendar-table.table_name');

        // Get the current date
        $currentDate = Carbon::now();

        // Subtract 5 years from the current date
        $this->startYear = (int) $currentDate->copy()->subYears(1)->toDateString();

        // Add 5 years to the current date
        $this->endYear = (int) $currentDate->copy()->addYears(1)->toDateString();

        $this->consoleCommand = "calendar:table --start={$this->startYear} --end={$this->endYear}";

        Artisan::call('migrate');
    }

    /** @test */
    public function it_runs_the_calendar_table_command()
    {
        // Arrange: Insert data into the database
        $exitCode = Artisan::call($this->consoleCommand);

        // Assert: Check if the command successfully run
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_checks_if_result_count_is_correct_in_database()
    {
        // Arrange: Insert data into the database
        Artisan::call($this->consoleCommand);

        // Act: Retrieve the data from the database
        $result = DB::table($this->tableName)->count();

        // Assert: Check if the result count is correct
        $this->assertEquals(1096, $result);
    }

    /** @test */
    public function test_it_asks_to_truncate_database_when_not_empty()
    {
        // Arrange: Insert data into the database
        Artisan::call($this->consoleCommand);

        // Arrange: Insert data into the database expect confirmation
        $this->artisan($this->consoleCommand)
            ->expectsQuestion('Do you wish to truncate the table', 'yes')
            ->assertExitCode(0);
    }
}
