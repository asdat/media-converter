<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\EncodingMediaJob;
use Illuminate\Support\Facades\File;
use \GuzzleHttp\Client;

class PutMediaIntoQueue extends Command
{
    /**
     * Media encoding configuration.
     *
     * @var array
     */
    protected $config;

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
        $this->config = config('media_encoding');
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

        $allowedExtensions = $this->config['allowed_input_extensions'];
        if (in_array($inputFileExtension, $allowedExtensions['audio'])) {
            $options = $this->config['output_options']['audio'];
        } elseif (in_array($inputFileExtension, $allowedExtensions['video'])) {
            $options = $this->config['output_options']['video'];
        } else {
            throw new \ErrorException('Unresolved input file extension: ' . $inputFileExtension);
        }

        $outputExtensionsArray = array_keys($options);

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

        $client = new Client();
        try {
            $client->request('GET', $this->option('input'));
        } catch (\Exception $e) {
            throw new \ErrorException('Input file is available. ' . $e->getMessage());
        }

        foreach ($options as $extension => $option) {
            $fullFile = $this->getFullFileData($path, $filename, $extension);

            dispatch((new EncodingMediaJob($this->option('input'), $fullFile, $option, $this->option('id'), $outputExtensionsArray))
                ->onQueue('default'));
        }
    }

    private function getFullFileData($path, $name, $ext)
    {
        return $path . $name . '.' . $ext;
    }
}