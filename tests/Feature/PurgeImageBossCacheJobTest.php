<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Noo\StatamicImageboss\Jobs\PurgeImageBossCacheJob;

beforeEach(function () {
    config()->set('statamic.imageboss.api_key', 'test-api-key');
});

it('sends delete request with api key header', function () {
    Http::fake(['*' => Http::response('', 200)]);

    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');
    $job->handle();

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://img.imageboss.me/test-source/assets/photo.jpg'
            && $request->header('imageboss-api-key')[0] === 'test-api-key';
    });
});

it('accepts an already absent origin path', function () {
    Http::fake(['*' => Http::response('', 404)]);

    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');
    $job->handle();
    Http::assertSentCount(1);
});

it('exposes failed purges to the queue worker with bounded retries and a finite lock', function (int $status) {
    Http::fake(['*' => Http::response('', $status)]);
    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');

    expect($job->tries)->toBe(4)
        ->and($job->backoff)->toBe([10, 30, 90])
        ->and($job->uniqueFor)->toBe(600)
        ->and(fn () => $job->handle())->toThrow(RequestException::class);
})->with([403, 429, 503]);

it('uses url as unique id', function () {
    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');

    expect($job->uniqueId())->toBe('https://img.imageboss.me/test-source/assets/photo.jpg');
});
