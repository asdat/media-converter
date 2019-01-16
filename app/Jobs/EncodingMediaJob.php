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
use Illuminate\Support\Facades\File;
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
     * The directories path for the file output.
     *
     * @var string
     */
    public $outputPath;

    /**
     * Id of the input file for database.
     *
     * @var integer
     */
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputFile, $id, $outputPath)
    {
        $this->inputFile = $inputFile;
        $this->outputPath = $outputPath;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $inputArray = explode('.', $this->inputFile);
        $inputFileExtension = strtolower(trim(array_pop($inputArray)));
        $filenameString = array_pop($inputArray);
        $filenameArray = explode('/', $filenameString);
        $filename = trim(array_pop($filenameArray));

        $allowedExtensions = config('media_encoding.allowed_extensions');

        if (in_array($inputFileExtension, $allowedExtensions['audio'])) {
            $options = [
                'mp3' => '-vn -ar 44100 -ac 2 -ab 192 -f mp3'
            ];
        } elseif (in_array($inputFileExtension, $allowedExtensions['video'])) {
            $options = [
                'mp4' => '-c:a aac -b:a 128k -c:v libx264 -crf 23 -f mp4',
                'webm' => '-vcodec libvpx -qscale:v 5  -acodec libvorbis -qscale:a 5 -f webm',
                'ogv' => '-codec:v libtheora -qscale:v 5 -codec:a libvorbis -qscale:a 5 -f ogg',
            ];
        } else {
            throw new \ErrorException('Unknown output file extension');
        }

        $path = 'output/' . $this->outputPath;
        $lastPathSymbol = substr($this->outputPath, -1);
        if ($lastPathSymbol !== '/') {
            $path .= '/';
        }

        if (!File::exists($path)) {
            if (!File::makeDirectory($path,  0755, true)) {
                throw new \ErrorException('Cannot create directory ' . $path);
            }
        }

        if (!File::isDirectory($path) && File::isWritable($path)) {
            throw new \ErrorException('Directory ' . $path . ' is not writable');
        }

        foreach ($options as $extension => $option) {
            $newFile = $filename . '.' . $extension;
            if (File::exists($path . $newFile)) {
                throw new \ErrorException('File ' . $newFile . ' already exists in directory ' . $path);
            }

            $command = 'ffmpeg -i ' . $this->inputFile . ' ' . $option . ' ' . $path . $newFile;

            Log::info($command);

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
}