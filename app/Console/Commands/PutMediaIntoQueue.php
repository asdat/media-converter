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
        $inputFileExtension = $this->getFileExtension($this->option('input'));
        $allowedExtensions = $this->config['allowed_input_extensions'];

        if (!in_array($inputFileExtension, $allowedExtensions['audio']) && !in_array($inputFileExtension, $allowedExtensions['video'])) {
            throw new \ErrorException(__('messages.exceptions.unresolved_extension', [
                'extension' => $inputFileExtension
            ]));
        }

        $outputPath = 'output/' . $this->option('output-path');
        $lastPathSymbol = substr($this->option('output-path'), -1);
        if ($lastPathSymbol !== '/') {
            $outputPath .= '/';
        }

        if (!File::exists($outputPath)) {
            if (!File::makeDirectory($outputPath,  0755, true)) {
                throw new \ErrorException(__('messages.exceptions.directory_creation_failure', [
                    'directory' => $outputPath
                ]));
            }
        }

        if (!File::isDirectory($outputPath) || !File::isWritable($outputPath)) {
            throw new \ErrorException(__('messages.exceptions.not_writable_directory', [
                'directory' => $outputPath
            ]));
        }

        $client = new Client();
        try {
            $client->request('GET', $this->option('input'));
        } catch (\Exception $e) {
            throw new \ErrorException(__('messages.exceptions.not available file', [
                'message' => $e->getMessage()
            ]));
        }

        dispatch((new EncodingMediaJob($this->option('input'), $outputPath, $this->option('id')))
            ->onQueue('default'));
    }

    private function getFileExtension($file)
    {
        $fileArray = explode('.', $file);

        return strtolower(trim(array_pop($fileArray)));
    }
}