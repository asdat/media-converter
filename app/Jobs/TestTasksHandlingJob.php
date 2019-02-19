<?php

namespace App\Jobs;

use Log;
use GearmanJob;
use demi\gearman\laravel5\GearmanFacade as Gearman;

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
        Gearman::runWorker('crop_image', function (GearmanJob $job) {
            Log::info($this->counter);
        });
    }
}
