<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::after(function (JobProcessed $event) {
            Log::info('test');
            // $event->connectionName
            // $event->job
            // $event->job->payload()

            $client = new \GuzzleHttp\Client();
            try {
                $response = $client->request('POST', config('external_api.url'), [
                    'body' => $event->job->payload()
                ]);

                $status = $response->getStatusCode();
                if ($status === 200) {
                    Log::info(__('messages.logs.request_sending_success', [
                        'url' => config('external_api.url'),
                        'id' =>  print_r($event->job->payload(), TRUE)
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
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
