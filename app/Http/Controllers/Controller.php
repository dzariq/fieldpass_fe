<?php

declare(strict_types=1);

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Traits\AuthorizationChecker;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\RabbitMQService;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, AuthorizationChecker;

    public function triggerEvent($data)
    {
        $message = json_encode($data);
        $queueName = 'admin';

        Log::info('BOARD | TRIGGER EVENT | '. $message);

        $rabbitMQ = new RabbitMQService();
        $rabbitMQ->sendMessage($queueName, $message);
    }
}
