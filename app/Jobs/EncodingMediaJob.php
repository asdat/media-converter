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
use \GuzzleHttp\Client;
use Log;


class EncodingMediaJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Media encoding configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The full path (url) of the input file.
     *
     * @var string
     */
    public $inputFile;

    /**
     * Path for output file.
     *
     * @var string
     */
    public $outputPath;

    /**
     * Id of the input file for request.
     *
     * @var string
     */
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputFile, $outputPath, $id)
    {
        $this->config = config('media_encoding');
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
        $inputFileExtension = $this->getFileExtension($this->inputFile);
        $options = $this->getEncodingOptions($inputFileExtension);
        $inputFilename = $this->getFileName($this->inputFile);

        foreach ($options as $extension => $option) {
            $command = 'ffmpeg -i ' . $this->inputFile . ' ' . $option . ' ' . $this->outputPath . $inputFilename . '.' . $extension;
            $this->runCommand($command);
        }

        $this->sendApiRequest();
    }

    private function getFileExtension($file)
    {
        $fileArray = explode('.', $file);

        return strtolower(trim(array_pop($fileArray)));
    }

    private function getFileName($file)
    {
        $this->getFileExtension($file);
        $filenameString = array_pop($file);
        $filenameArray = explode('/', $filenameString);

        return trim(array_pop($filenameArray));
    }

    private function getEncodingOptions($extension)
    {
        $allowedExtensions = $this->config['allowed_input_extensions'];
        if (in_array($extension, $allowedExtensions['audio'])) {
            return $this->config['output_options']['audio'];
        } elseif (in_array($extension, $allowedExtensions['video'])) {
            return $this->config['output_options']['video'];
        }
    }

    private function runCommand($command)
    {
        $process = new Process(trim($command));
        $process->setTimeout(3600);
        $process->setIdleTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function sendApiRequest()
    {
        $client = new Client();
        try {
            $response = $client->request('POST', config('external_api.url'), [
                'id' => $this->id
            ]);

            $status = $response->getStatusCode();
            if ($status === 200) {
                Log::info(__('messages.logs.request_sending_success', [
                    'url' => config('external_api.url')
                ]));
            } else {
                Log::error(__('messages.logs.request_sending_failure', [
                    'url' => config('external_api.url')
                ]));
            }
        } catch (\Exception $e) {
            Log::error(__('messages.logs.request_sending_failure', [
                'url' => config('external_api.url')
            ]));
        }
    }
}