<?php

namespace App\Jobs;

use Log;

class TestTasksHandlingJob extends Job
{
    public $counter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($counter)
    {
        $this->counter = $counter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info($this->counter);
    }
}
