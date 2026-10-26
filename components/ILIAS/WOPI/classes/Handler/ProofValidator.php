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

use ILIAS\WOPI\Discovery\ProofKey;
use ILIAS\WOPI\Discovery\ProofKeys;

/**
 * Verifies that a request was sent by the WOPI client, see
 * https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/online/scenarios/proofkeys
 *
 * The client signs the access token, the URL and a timestamp of each request with its private
 * key. Without proof keys in the discovery there is nothing to verify against, every request
 * is accepted then.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ProofValidator
{
    /**
     * X-WOPI-TimeStamp counts the 100 nanosecond intervals since 0001-01-01 (.NET ticks).
     */
    private const TICKS_AT_UNIX_EPOCH = 621_355_968_000_000_000;
    private const TICKS_PER_SECOND = 10_000_000;
    private const MAX_AGE = 20 * 60;

    /**
     * @var \Closure(): int
     */
    private \Closure $clock;

    /**
     * @param \Closure(): int|null $clock returns the current unix timestamp, for testing
     */
    public function __construct(
        private ?ProofKeys $keys,
        ?\Closure $clock = null
    ) {
        $this->clock = $clock ?? static fn(): int => time();
    }

    public function withKeys(ProofKeys $keys): self
    {
        $clone = clone $this;
        $clone->keys = $keys;

        return $clone;
    }

    /**
     * @param string $url the URL the client requested, including the query string
     */
    public function isValid(
        string $access_token,
        string $url,
        ?string $timestamp,
        ?string $proof,
        ?string $old_proof
    ): bool {
        if ($this->keys === null) {
            return true;
        }
        if ($timestamp === null || $proof === null || !ctype_digit($timestamp)) {
            return false;
        }
        $age = ($this->clock)() - intdiv((int) $timestamp - self::TICKS_AT_UNIX_EPOCH, self::TICKS_PER_SECOND);
        if (abs($age) > self::MAX_AGE) {
            return false;
        }

        $expected = self::expected($access_token, $url, (int) $timestamp);

        // the current key signed the proof, or it signed the old proof (the host has the new key,
        // the client still signs with the old one as well), or the old key signed the proof
        // (the client has rotated its keys and the host has not seen the new discovery yet)
        return $this->verify($expected, $proof, $this->keys->current)
            || ($old_proof !== null && $this->verify($expected, $old_proof, $this->keys->current))
            || ($this->keys->old !== null && $this->verify($expected, $proof, $this->keys->old));
    }

    public static function expected(string $access_token, string $url, int $timestamp): string
    {
        $url = strtoupper($url);

        return pack('N', strlen($access_token)) . $access_token
            . pack('N', strlen($url)) . $url
            . pack('N', 8) . pack('J', $timestamp);
    }

    private function verify(string $expected, string $signature, ProofKey $key): bool
    {
        $decoded = base64_decode($signature, true);
        if ($decoded === false || $decoded === '') {
            return false;
        }
        try {
            $public_key = openssl_pkey_get_public($key->toPem());
        } catch (\InvalidArgumentException) {
            return false;
        }
        if ($public_key === false) {
            return false;
        }

        return openssl_verify($expected, $decoded, $public_key, OPENSSL_ALGO_SHA256) === 1;
    }
}
