<?php

use Database\Factories\DomainFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('should store domains', function () {
    $domains = DomainFactory::new()->count(5)->create();

    expect($domains->count())->toBe(5);
});

it('should search for domain', function () {
    DomainFactory::new()->create(['domain' => 'example.com']);
    DomainFactory::new()->count(2)->create();

    $response = $this->get('/api/domains?search=example');

    $response
        ->assertOk()
        ->assertJsonFragment(['domain' => 'example.com']);
});

it('should fail to search for domain', function () {
    DomainFactory::new()->count(5)->create();

    $response = $this
        ->get('/api/domains?search=nonexistentdomain')
        ->assertOk();

    expect($response['data'])->toBeEmpty();
});

it('should return paginated response structure', function () {
    DomainFactory::new()->count(150)->create();

    $response = $this->get('/api/domains');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data'  => [['domain', 'rank']],
            'meta'  => ['current_page', 'from', 'last_page', 'per_page', 'total'],
            'links' => ['first', 'last', 'prev', 'next'],
        ]);
});

it('rejects invalid pagination params', function () {
    $response = $this->getJson('/api/domains?page=-5&per_page=1000');

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['page', 'per_page']);
});
