<?php

namespace Noo\StatamicImageboss\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurgeImageBossCacheJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $url
    ) {}

    public function uniqueId(): string
    {
        return $this->url;
    }

    public function handle(): void
    {
        $response = Http::withHeaders([
            'imageboss-api-key' => config('statamic.imageboss.api_key'),
        ])->delete($this->url);

        if ($response->failed()) {
            Log::error('ImageBoss cache purge failed', [
                'url' => $this->url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
