<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Events\ChatMessageEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function stream()
    {
        ini_set('max_execution_time', 0);
        return response()->stream(function () {
            try {
                // Start output buffering
                if (ob_get_level() == 0) {
                    ob_start();
                }
                // Send ping event
                echo "data: " . json_encode([
                    'event' => 'ping',
                    'data' => ['timestamp' => time()]
                ]) . "\n\n";
                ob_flush();
                flush();
                sleep(1);
                // Listen for ChatMessageEvent
                Redis::subscribe(['sse-event-channel', 'ping-channel'], function (string $message, string $channel) {
                    if($channel === config('database.redis.options.prefix') . 'sse-event-channel') {
                        echo "data: " . $message . "\n\n";
                        ob_flush();
                        flush();
                    } else if($channel === config('database.redis.options.prefix') . 'ping-channel') {
                        echo $message . "\n\n";
                        ob_flush();
                        flush();
                    }
                });
            } catch (\Exception $th) {

            } finally {
                // Clean up
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function send(Request $request)
    {
        $message = [
            'event' => 'new-message',
            'data' => [
                'user' => Auth::check() ? Auth::user()->name : 'Anonymous',
                'message' => $request->input('message'),
                'timestamp' => now()->toIso8601String()
            ]
        ];

        // Emit the event directly
        Redis::publish('sse-event-channel', json_encode($message));

        return response()->json(['status' => 'success']);
    }
}
