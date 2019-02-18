<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\TestTasksHandlingJob;

class PutTestTaskIntoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:put';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put test task into queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for ($i = 1; $i <= 100000; $i++) {
            dispatch((new TestTasksHandlingJob($i))
                ->onQueue('default'));
        }
    }
}