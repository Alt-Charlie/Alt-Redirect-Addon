<?php

use AltDesign\AltRedirect\Contracts\RepositoryInterface;
use Statamic\Facades\Site;

it('can redirect a simple path', function (string $driver) {
    config(['alt-redirect.driver' => $driver]);

    $repository = app(RepositoryInterface::class);
    $repository->save('redirects', [
        'id' => 'simple-test-'.$driver,
        'from' => '/old-path-'.$driver,
        'to' => '/new-path-'.$driver,
        'redirect_type' => 301,
        'sites' => ['default'],
    ]);

    $this->get('/old-path-'.$driver)
        ->assertRedirect('/new-path-'.$driver)
        ->assertStatus(301);
})->with(['file', 'database']);

it('can redirect with regex', function (string $driver) {
    config(['alt-redirect.driver' => $driver]);

    $repository = app(RepositoryInterface::class);
    $repository->save('redirects', [
        'id' => 'regex-test-'.$driver,
        'from' => '/old-'.$driver.'/(.*)',
        'to' => '/new-'.$driver.'/$1',
        'redirect_type' => 302,
        'sites' => ['default'],
    ]);

    $this->get('/old-'.$driver.'/something')
        ->assertRedirect('/new-'.$driver.'/something')
        ->assertStatus(302);
})->with(['file', 'database']);

it('can redirect with query strings in regex', function (string $driver) {
    config(['alt-redirect.driver' => $driver]);

    $repository = app(RepositoryInterface::class);
    $repository->save('redirects', [
        'id' => 'query-regex-test-'.$driver,
        'from' => '/test-'.$driver.'\?source=(.*)',
        'to' => '/beans-'.$driver.'/$1',
        'redirect_type' => 301,
        'sites' => ['default'],
    ]);

    // It should match and redirect, but since 'source' is not stripped, it's added back to the target
    $this->get('/test-'.$driver.'?source=mandem')
        ->assertRedirect('/beans-'.$driver.'/mandem?source=mandem')
        ->assertStatus(301);
})->with(['file', 'database']);

it('can strip query strings', function (string $driver) {
    config(['alt-redirect.driver' => $driver]);

    $repository = app(RepositoryInterface::class);
    $repository->save('query-strings', [
        'id' => 'strip-gclid-'.$driver,
        'query_string' => 'gclid',
        'strip' => true,
        'sites' => ['default'],
    ]);

    $repository->save('redirects', [
        'id' => 'simple-strip-test-'.$driver,
        'from' => '/old-strip-'.$driver,
        'to' => '/new-strip-'.$driver,
        'redirect_type' => 301,
        'sites' => ['default'],
    ]);

    $this->get('/old-strip-'.$driver.'?gclid=123&other=456')
        ->assertRedirect('/new-strip-'.$driver.'?other=456')
        ->assertStatus(301);
})->with(['file', 'database']);

it('can handle site specific redirects', function (string $driver) {
    config(['alt-redirect.driver' => $driver]);

    $repository = app(RepositoryInterface::class);
    $repository->save('redirects', [
        'id' => 'site-test-'.$driver,
        'from' => '/only-default-'.$driver,
        'to' => '/matched-'.$driver,
        'redirect_type' => 301,
        'sites' => ['default'],
    ]);

    Site::setCurrent('default');
    $this->get('/only-default-'.$driver)->assertRedirect('/matched-'.$driver);

    Site::setCurrent('other');
    // If not default site, it should not match and return 404
    $this->get('/only-default-'.$driver)->assertNotFound();
})->with(['file', 'database']);

it('forgives unescaped question marks in regex', function (string $driver) {
    config(['alt-redirect.driver' => $driver]);

    $repository = app(RepositoryInterface::class);
    $repository->save('redirects', [
        'id' => 'unescaped-test-'.$driver,
        'from' => '/test-unescaped-'.$driver.'?source=(.*)', // Unescaped ?
        'to' => '/new-test-'.$driver.'/$1',
        'redirect_type' => 301,
        'sites' => ['default'],
    ]);

    $this->get('/test-unescaped-'.$driver.'?source=mandem')
        ->assertRedirect('/new-test-'.$driver.'/mandem?source=mandem');
})->with(['file', 'database']);
