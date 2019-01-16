<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 09.01.2019
 * Time: 23:52
 */

namespace App\Jobs;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;


class EncodingMediaJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The full path (url) of the input file.
     *
     * @var string
     */
    public $inputFile;

    /**
     * File output data.
     *
     * @var string
     */
    public $outputFile;

    /**
     * Encoding options.
     *
     * @var string
     */
    public $options;

    /**
     * Id of the input file for request.
     *
     * @var integer
     */
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputFile, $outputFile, $options, $id)
    {
        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;
        $this->options = $options;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $command = 'ffmpeg -i ' . $this->inputFile . ' ' . $this->options . ' ' . $this->outputFile;

        $process = new Process(trim($command));
        $process->setTimeout(3600);
        $process->setIdleTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // TODO: Send API-request
    }
}