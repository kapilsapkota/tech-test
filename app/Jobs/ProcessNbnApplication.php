<?php

namespace App\Jobs;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessNbnApplication implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->application->status !== 'order') {
            return;
        }

        $endpoint = config('endpoint.nbn_b2b_endpoint');

        $response = Http::post($endpoint, [
            'address_1' => $this->application->address_1,
            'address_2' => $this->application->address_2,
            'city' => $this->application->city,
            'state' => $this->application->state,
            'postcode' => $this->application->postcode,
            'plan_id' => $this->application->plan_id,
        ]);

        if ($response->successful() && $response->json('status') == "Successful") {
            $order_id = $response->json('id');
            $this->application->order_id = $order_id;
            $this->application->status = 'complete';
            $this->application->save();
        } else {
            $this->application->status = 'order_failed';
            $this->application->save();
        }
    }
}
