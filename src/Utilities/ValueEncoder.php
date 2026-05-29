<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Str;

class ValueEncoder
{
    /**
     * Mapping table for digits to letters encoding
     */
    const DIGIT_MAP = [
        '0' => 'A',
        '1' => 'B',
        '2' => 'C',
        '3' => 'D',
        '4' => 'E',
        '5' => 'F',
        '6' => 'G',
        '7' => 'H',
        '8' => 'I',
        '9' => 'J',
    ];

    /**
     * Mapping table for letters to digits decoding
     */
    const LETTER_MAP = [
        'A' => '0',
        'B' => '1',
        'C' => '2',
        'D' => '3',
        'E' => '4',
        'F' => '5',
        'G' => '6',
        'H' => '7',
        'I' => '8',
        'J' => '9',
    ];

    /**
     * Pattern for token validation: [random]-[encoded_id]-[random]
     */
    const PATTERN = '/^[a-zA-Z0-9]+-[a-zA-Z0-9]+-[a-zA-Z0-9]+$/';

    /**
     * Separator used in a token structure
     */
    const SEPARATOR = '-';

    /**
     * Generate an encoded token with the given ID
     *
     * @param mixed $id The ID to encode (nullable)
     * @param int $prefixLength Length of the prefix random string (nullable, defaults to 5)
     * @param int $suffixLength Length of the suffix random string (nullable, defaults to 5)
     * @return string
     */
    public static function generateToken(mixed $id, int $prefixLength = 5, int $suffixLength = 5): string
    {
        if (empty($id)) {
            return '';
        }
        $encodedId = self::encode($id);
        return Str::random($prefixLength).
            self::SEPARATOR.
            $encodedId.
            self::SEPARATOR.
            Str::random($suffixLength);
    }

    /**
     * @param $value
     * @return string
     */
    public static function encode($value): string
    {
        if (empty($value)) {
            return '';
        }

        $value = (string) $value;
        $encoded = '';

        for ($i = 0; $i < strlen($value); $i++) {
            $digit = $value[$i];
            $encoded .= self::DIGIT_MAP[$digit] ?? $digit;
        }

        return $encoded;
    }

    /**
     * Validate if a token matches the expected ID
     *
     * @param string|null $token The token to validate (nullable)
     * @param int|null $expectedId The expected ID to match against (nullable)
     * @return bool
     */
    public static function validateToken(?string $token = null, ?int $expectedId = null): bool
    {
        if ($token === null || $expectedId === null) {
            return false;
        }

        $extractedId = self::extract($token);
        return $extractedId !== null && (int) $extractedId === $expectedId;
    }

    /**
     * Extract the ID from a token
     *
     * @param string|null $token The token to extract from (nullable)
     * @return string|null
     */
    public static function extract(?string $token = null): ?string
    {
        if ($token === null || !preg_match(self::PATTERN, $token)) {
            return null;
        }

        $parts = explode(self::SEPARATOR, $token);

        if (count($parts) !== 3) {
            return null;
        }

        return self::decode($parts[1]);
    }

    /**
     * Decode an encoded string back to digits
     *
     * @param string|null $encodedString The encoded string to decode (nullable)
     * @return string
     */
    public static function decode(?string $encodedString = null): string
    {
        if ($encodedString === null || $encodedString === '') {
            return '';
        }

        $decoded = '';

        for ($i = 0; $i < strlen($encodedString); $i++) {
            $char = $encodedString[$i];
            $decoded .= self::LETTER_MAP[$char] ?? $char;
        }

        return $decoded;
    }

    /**
     * Parse a token into its components
     *
     * @param string|null $token The token to parse (nullable)
     * @return array|null
     */
    public static function parseToken(?string $token = null): ?array
    {
        if ($token === null || !preg_match(self::PATTERN, $token)) {
            return null;
        }

        $parts = explode(self::SEPARATOR, $token);

        if (count($parts) !== 3) {
            return null;
        }

        return [
            'prefix'     => $parts[0],
            'encoded_id' => $parts[1],
            'decoded_id' => self::decode($parts[1]),
            'suffix'     => $parts[2],
        ];
    }

    /**
     * Check if a token has valid format
     *
     * @param string|null $token The token to check (nullable)
     * @return bool
     */
    public static function isValidFormat(?string $token = null): bool
    {
        if ($token === null) {
            return false;
        }

        return (bool) preg_match(self::PATTERN, $token);
    }
}
