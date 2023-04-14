<?php

namespace App\Jobs;

use App\Models\Settings;
use App\Models\User;
use App\Traits\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ImportReadyNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Notification;

    private $shop_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop_id)
    {
        $this->shop_id = $shop_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sellers = User::whereHas('shop', function ($q){
            $q->where('id',$this->shop_id);
        })->whereNotNull('firebase_token')->pluck('firebase_token');

        $this->sendNotification($sellers->toArray(), 'Excel file imported successfully');
    }

}
