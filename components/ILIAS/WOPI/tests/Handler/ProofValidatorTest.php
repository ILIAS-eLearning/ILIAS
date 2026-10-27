<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\WOPI\Handler;

use PHPUnit\Framework\TestCase;
use ILIAS\WOPI\Discovery\ProofKey;
use ILIAS\WOPI\Discovery\ProofKeys;

final class ProofValidatorTest extends TestCase
{
    private const TOKEN = 'the-access-token';
    private const URL = 'https://ilias.example.org/wopi/index.php/files/rid?access_token=the-access-token';
    private const NOW = 1_791_500_000;

    /** @var array{0: \OpenSSLAsymmetricKey, 1: ProofKey} */
    private static array $current;
    /** @var array{0: \OpenSSLAsymmetricKey, 1: ProofKey} */
    private static array $old;

    public static function setUpBeforeClass(): void
    {
        self::$current = self::keyPair();
        self::$old = self::keyPair();
    }

    /**
     * @return array{0: \OpenSSLAsymmetricKey, 1: ProofKey}
     */
    private static function keyPair(): array
    {
        $private = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $rsa = openssl_pkey_get_details($private)['rsa'];

        return [$private, new ProofKey(base64_encode($rsa['n']), base64_encode($rsa['e']))];
    }

    private static function ticks(int $unix): int
    {
        return $unix * 10_000_000 + 621_355_968_000_000_000;
    }

    private static function sign(\OpenSSLAsymmetricKey $key, string $url = self::URL, int $unix = self::NOW): string
    {
        openssl_sign(ProofValidator::expected(self::TOKEN, $url, self::ticks($unix)), $signature, $key, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    private function validator(?ProofKeys $keys): ProofValidator
    {
        return new ProofValidator($keys, static fn(): int => self::NOW);
    }

    private function keys(bool $with_old = true): ProofKeys
    {
        return new ProofKeys(self::$current[1], $with_old ? self::$old[1] : null);
    }

    public function testWithoutProofKeysEveryRequestIsAccepted(): void
    {
        $this->assertTrue($this->validator(null)->isValid(self::TOKEN, self::URL, null, null, null));
    }

    public function testProofSignedWithTheCurrentKeyIsValid(): void
    {
        $this->assertTrue($this->validator($this->keys())->isValid(
            self::TOKEN,
            self::URL,
            (string) self::ticks(self::NOW),
            self::sign(self::$current[0]),
            null
        ));
    }

    public function testTheUrlIsComparedCaseInsensitive(): void
    {
        $this->assertTrue($this->validator($this->keys())->isValid(
            self::TOKEN,
            strtolower(self::URL),
            (string) self::ticks(self::NOW),
            self::sign(self::$current[0]),
            null
        ));
    }

    public function testOldProofSignedWithTheCurrentKeyIsValid(): void
    {
        $this->assertTrue($this->validator($this->keys())->isValid(
            self::TOKEN,
            self::URL,
            (string) self::ticks(self::NOW),
            self::sign(self::$old[0]),
            self::sign(self::$current[0])
        ));
    }

    public function testProofSignedWithTheOldKeyIsValid(): void
    {
        $this->assertTrue($this->validator($this->keys())->isValid(
            self::TOKEN,
            self::URL,
            (string) self::ticks(self::NOW),
            self::sign(self::$old[0]),
            null
        ));
        $this->assertFalse($this->validator($this->keys(false))->isValid(
            self::TOKEN,
            self::URL,
            (string) self::ticks(self::NOW),
            self::sign(self::$old[0]),
            null
        ));
    }

    public function testProofForAnotherUrlIsInvalid(): void
    {
        $this->assertFalse($this->validator($this->keys())->isValid(
            self::TOKEN,
            self::URL . '&other=1',
            (string) self::ticks(self::NOW),
            self::sign(self::$current[0]),
            null
        ));
    }

    public function testProofForAnotherTokenIsInvalid(): void
    {
        $this->assertFalse($this->validator($this->keys())->isValid(
            'another-token',
            self::URL,
            (string) self::ticks(self::NOW),
            self::sign(self::$current[0]),
            null
        ));
    }

    public function testProofOlderThanTwentyMinutesIsInvalid(): void
    {
        $then = self::NOW - 21 * 60;

        $this->assertFalse($this->validator($this->keys())->isValid(
            self::TOKEN,
            self::URL,
            (string) self::ticks($then),
            self::sign(self::$current[0], self::URL, $then),
            null
        ));
    }

    public function testMissingOrMalformedHeadersAreInvalid(): void
    {
        $validator = $this->validator($this->keys());
        $proof = self::sign(self::$current[0]);

        $this->assertFalse($validator->isValid(self::TOKEN, self::URL, null, $proof, null));
        $this->assertFalse($validator->isValid(self::TOKEN, self::URL, (string) self::ticks(self::NOW), null, null));
        $this->assertFalse($validator->isValid(self::TOKEN, self::URL, 'yesterday', $proof, null));
        $this->assertFalse($validator->isValid(self::TOKEN, self::URL, (string) self::ticks(self::NOW), 'not base64!', null));
    }
}
