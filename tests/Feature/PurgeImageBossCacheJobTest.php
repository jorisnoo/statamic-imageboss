<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

it('logs error on failed request', function () {
    Http::fake(['*' => Http::response('Not Found', 404)]);
    Log::shouldReceive('error')
        ->once()
        ->with('ImageBoss cache purge failed', Mockery::on(function ($context) {
            return $context['url'] === 'https://img.imageboss.me/test-source/assets/photo.jpg'
                && $context['status'] === 404;
        }));

    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');
    $job->handle();
});

it('does not log on successful request', function () {
    Http::fake(['*' => Http::response('', 200)]);
    Log::shouldReceive('error')->never();

    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');
    $job->handle();
});

it('uses url as unique id', function () {
    $job = new PurgeImageBossCacheJob('https://img.imageboss.me/test-source/assets/photo.jpg');

    expect($job->uniqueId())->toBe('https://img.imageboss.me/test-source/assets/photo.jpg');
});
