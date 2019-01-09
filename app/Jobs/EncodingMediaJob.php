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


class EncodingMediaJob
{
    public $input;
    public $output;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input, $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $inputArray = explode('.', $this->output);
        $ext = $inputArray[count($inputArray) - 1];

        if ($ext === 'mp3') {
            $options = '-vn -ar 44100 -ac 2 -ab 192 -f mp3';
        } elseif ($ext === 'mp4') {
            $options = '-c:a aac -b:a 128k -c:v libx264 -crf 23 -f mp4';
        } elseif ($ext === 'webm') {
            $options = '-vcodec libvpx -qscale:v 5  -acodec libvorbis -qscale:a 5 -f webm';
        } elseif ($ext === 'ogg' || $ext = 'ogv') {
            $options = '-codec:v libtheora -qscale:v 5 -codec:a libvorbis -qscale:a 5 -f ogg';
        } else {
            throw new \Exception('Unknown output file extension');
        }

        $command = 'docker run -v $PWD:/tmp jrottenberg/ffmpeg:3.4-scratch  -i ' . $this->input .  ' ' . $options . ' - > ' . $this->output;

        $process = new Process($command);
        $process->setTimeout(3600);
        $process->setIdleTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}