<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\EncodingMediaJob;

class EncodeMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:run {input} {output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encode audio and video files to needed formats';

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
        /*$this->comment('Encoding file...');
        $inputArray = explode('.', $this->argument('output'));
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

        $command = 'docker run -v $PWD:/tmp jrottenberg/ffmpeg:3.4-scratch  -i ' . $this->argument('input') .  ' ' . $options . ' - > ' . $this->argument('output');

        $process = new Process($command);
        $process->setTimeout(3600);
        $process->setIdleTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->comment('File has been encoded');*/

        dispatch(new EncodingMediaJob($this->argument('input'), $this->argument('output')));
    }
}