<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\EncodingMediaJob;

class PutMediaIntoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:put {--input=} {--id=} {--output-path=} {--queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put audio and video files to encoding queue';

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
        dispatch((new EncodingMediaJob($this->option('input'), $this->option('id'), $this->option('output-path')))
            ->onQueue($this->option('queue')));
    }
}