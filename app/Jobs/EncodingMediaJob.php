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
     * @var string
     */
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputFile, $outputFile, $options, $id)
    {
        $this->config = config('media_encoding');
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

        $inputArray = explode('.', $this->inputFile);
        $inputFileExtension = strtolower(trim(array_pop($inputArray)));
        $allowedExtensions = $this->config['allowed_input_extensions'];

        if (in_array($inputFileExtension, $allowedExtensions['audio'])) {
            $outputExtGroup = array_keys($this->config['output_options']['audio']);
        } else {
            $outputExtGroup = array_keys($this->config['output_options']['video']);
        }

        $outputFileWithoutExtension = explode('.', $this->outputFile);
        array_pop($outputFileWithoutExtension);
        $outputFileWithoutExtension = implode('.', $outputFileWithoutExtension);

        $sendRequestFlag = true;
        foreach ($outputExtGroup as $extension) {
            Log::info($outputFileWithoutExtension . '.' . $extension . ' + ' . $sendRequestFlag);
            if (!File::exists($outputFileWithoutExtension . '.' . $extension)) {
                $sendRequestFlag = false;
                break;
            }
        }

        if ($sendRequestFlag) {
            $client = new Client();
            try {
                $client->request(config('external_api.request_method'), config('external_api.url'), [
                    'id' => $this->id
                ]);

                Log::info('File ' . $this->outputFile . ' was encoded.');
            } catch (\Exception $e) {
                Log::error('Request was not sended at ' . date('H:i:s' . ' for file ' . $this->outputFile));
            }
        }
    }
}