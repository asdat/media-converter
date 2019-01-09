<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 09.01.2019
 * Time: 21:09
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PutMediaIntoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:put {path} {id}';

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

    }
}