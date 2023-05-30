<?php

namespace App\Traits;

use Throwable;
use Illuminate\Support\Facades\Log;

trait Loggable
{
    /**
     * @param Throwable $e
     * @return void
     */
    public function error(Throwable $e): void
    {
        Log::error($e->getMessage(), [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);
    }

    /**
     * @return void
     */
    public function info(): void
    {

    }
}

