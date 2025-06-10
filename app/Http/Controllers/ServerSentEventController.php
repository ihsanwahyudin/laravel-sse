<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerSentEventController extends Controller
{
    public function ping()
    {
        ini_set('max_execution_time', 0);
        return response()->stream(function () {
            // Start output buffering
            if (ob_get_level() == 0) {
                ob_start();
            }
            $counter = 1;
            while (true) {
                echo "data: {\"ping\": \"" . now() . "\", \"counter\": \"" . $counter . "\"}\n\n";
                $counter++;
                ob_flush();
                flush();
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    public function chatCounter(Request $request)
    {
        ini_set('max_execution_time', 0);
        return response()->stream(function () {
            // Start output buffering
            if (ob_get_level() == 0) {
                ob_start();
            }
            $counter = 1;
            $message = [
                'user' => Auth::check() ? Auth::user()->name : 'Anonymous',
                'timestamp' => now()->toIso8601String()
            ];
            while (true) {
                $message['message'] = "$counter";
                echo "data: " . json_encode($message) . "\n\n";
                $counter++;
                ob_flush();
                flush();
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
