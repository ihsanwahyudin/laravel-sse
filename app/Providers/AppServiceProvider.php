<?php

namespace App\Providers;

use App\Events\ChatMessageEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Event::listen(function (ChatMessageEvent $event) {
        //     Log::info("Broadcasting message: " . json_encode($event->message));
        // });
    }
}
