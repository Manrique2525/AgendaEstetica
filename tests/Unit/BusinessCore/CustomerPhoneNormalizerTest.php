<?php

use App\Support\CustomerPhoneNormalizer;

it('normalizes approved Mexican phone formats', function (string $input, string $expected): void {
    expect((new CustomerPhoneNormalizer)->normalize($input))->toBe($expected);
})->with([
    ['9932294158', '+529932294158'],
    ['993 229 4158', '+529932294158'],
    ['(993) 229-4158', '+529932294158'],
    ['52 993 229 4158', '+529932294158'],
    ['+52 993 229 4158', '+529932294158'],
    ['0052 993 229 4158', '+529932294158'],
]);

it('preserves an explicit international number', function (): void {
    expect((new CustomerPhoneNormalizer)->normalize('+1 (415) 555-2671'))
        ->toBe('+14155552671');
});

it('rejects invalid or ambiguous phone inputs', function (string $input): void {
    expect(fn () => (new CustomerPhoneNormalizer)->normalize($input))
        ->toThrow(InvalidArgumentException::class);
})->with([
    '',
    '   ',
    'abc',
    '993ABC4158',
    '++529932294158',
    '52+9932294158',
    '123456789',
    '12345678901',
    '+1234567890123456',
    '+52/9932294158',
]);
