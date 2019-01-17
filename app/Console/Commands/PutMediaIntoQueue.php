<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\EncodingMediaJob;
use Illuminate\Support\Facades\File;
use \GuzzleHttp\Client;

class PutMediaIntoQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:put {--input=} {--id=} {--output-path=}';

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
        $inputArray = explode('.', $this->option('input'));
        $inputFileExtension = strtolower(trim(array_pop($inputArray)));
        $filenameString = array_pop($inputArray);
        $filenameArray = explode('/', $filenameString);
        $filename = trim(array_pop($filenameArray));

        $allowedExtensions = config('media_encoding.allowed_extensions');

        if (in_array($inputFileExtension, $allowedExtensions['audio'])) {
            $options = [
                'mp3' => '-y -vn -ar 44100 -ac 2 -ab 192 -f mp3'
            ];
        } elseif (in_array($inputFileExtension, $allowedExtensions['video'])) {
            $options = [
                'mp4' => '-y -c:a aac -b:a 128k -c:v libx264 -crf 23 -f mp4',
                'webm' => '-y -vcodec libvpx -qscale:v 5  -acodec libvorbis -qscale:a 5 -f webm',
                'ogv' => '-y -codec:v libtheora -qscale:v 5 -codec:a libvorbis -qscale:a 5 -f ogg',
            ];
        } else {
            throw new \ErrorException('Unresolved input file extension: ' . $inputFileExtension);
        }

        $path = 'output/' . $this->option('output-path');
        $lastPathSymbol = substr($this->option('output-path'), -1);
        if ($lastPathSymbol !== '/') {
            $path .= '/';
        }

        if (!File::exists($path)) {
            if (!File::makeDirectory($path,  0755, true)) {
                throw new \ErrorException('Cannot create directory: ' . $path);
            }
        }

        if (!File::isDirectory($path) && File::isWritable($path)) {
            throw new \ErrorException('Directory ' . $path . ' is not writable');
        }

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $this->option('input'));
        } catch (\Exception $e) {
            throw new \ErrorException ($e->getRequest());
        }
        $status = $response->getStatusCode();
        if ($status !== 200) {
            throw new \ErrorException('Input file is available');
        }

        foreach ($options as $extension => $option) {
            $fullFile = $this->glueFilePathNameAndExtension($path, $filename, $extension);

            dispatch((new EncodingMediaJob($this->option('input'), $fullFile, $option, $this->option('id')))
                ->onQueue('default'));
        }
    }

    private function glueFileNameAndExtension($name, $ext)
    {
        return $name . '.' . $ext;
    }

    private function glueFilePathNameAndExtension($path, $name, $ext)
    {
        return $path . $this->glueFileNameAndExtension($name, $ext);
    }
}