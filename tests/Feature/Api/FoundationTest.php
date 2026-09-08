<?php

it('returns the health payload', function (): void {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertExactJson([
            'data' => ['status' => 'ok'],
        ]);
});

it('returns a JSON 404 for an unknown API route', function (): void {
    $this->getJson('/api/v1/non-existent')
        ->assertNotFound()
        ->assertJsonStructure(['message'])
        ->assertHeader('content-type', 'application/json');
});
