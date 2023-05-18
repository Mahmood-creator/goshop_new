<?php

namespace App\Traits;

use App\Models\Settings;
use Illuminate\Support\Facades\Http;

trait Notification
{
    private string $url = 'https://fcm.googleapis.com/fcm/send';

    public function sendNotification($receivers = [], $message = '', $title = null): string
    {

        $server_key = $this->firebaseKey();
        $fields = [
            'registration_ids' => $receivers,
            "notification" => [
                "body" => $message,
                "title" => $title,
            ]
        ];

        $headers = [
            'Authorization' => 'key=' . $server_key,
            'Content-Type' => 'application/json'
        ];

        $response = Http::withHeaders($headers)->post($this->url, $fields);
        $response = $response->body();

        info('NOTIFICATION LOG REQUEST', [$fields, $headers]);
        info('NOTIFICATION LOG RESPONSE', [$response]);
        return $response;
    }

    private function firebaseKey()
    {
        return Settings::adminSettings()->where('key', 'server_key')->pluck('value')->first();
    }
}
