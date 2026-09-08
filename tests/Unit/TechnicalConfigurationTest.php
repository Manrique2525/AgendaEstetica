<?php

it('uses the approved technical runtime defaults', function (): void {
    expect(config('app.timezone'))->toBe('UTC')
        ->and(config('filesystems.default'))->toBe('local');
});
