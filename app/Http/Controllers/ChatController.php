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
        $response = new StreamedResponse(function() {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            // Start output buffering
            if (ob_get_level() == 0) {
                ob_start();
            }

            try {
                echo "event: ping\n";
                echo "data: " . json_encode(['timestamp' => time()]) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                sleep(1);
                // Listen for ChatMessageEvent
                Redis::subscribe(['test-channel'], function (string $message, string $channel) {
                    if (connection_aborted()) {
                        throw new \Exception('Connection aborted');
                    }
                    echo "data: " . $message . "\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                });
            } catch (\Exception $th) {
                //throw $th;
            } finally {
                // Clean up
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
            }
        });

        // Set response headers
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    public function send(Request $request)
    {
        $message = [
            'user' => Auth::check() ? Auth::user()->name : 'Anonymous',
            'message' => $request->input('message'),
            'timestamp' => now()->toIso8601String()
        ];

        // Emit the event directly
        Redis::publish('test-channel', json_encode($message));

        return response()->json(['status' => 'success']);
    }
} 