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

namespace ILIAS\WOPI\Discovery;

use PHPUnit\Framework\TestCase;

final class ProofKeyRefreshTest extends TestCase
{
    /** @var array<string, string> */
    private array $stored = [];
    private int $now = 1_000_000;
    private int $fetches = 0;
    private ?ProofKeys $discovered;

    protected function setUp(): void
    {
        $this->discovered = new ProofKeys(new ProofKey('bmV3', 'AQAB'));
    }

    private function refresh(bool $failing = false): ProofKeyRefresh
    {
        $settings = $this->createStub(\ilSetting::class);
        $settings->method('get')->willReturnCallback(
            fn(string $key, ?string $default = null): ?string => $this->stored[$key] ?? $default
        );
        $settings->method('set')->willReturnCallback(function (string $key, string $value): void {
            $this->stored[$key] = $value;
        });
        $settings->method('delete')->willReturnCallback(function (string $key): void {
            unset($this->stored[$key]);
        });

        return new ProofKeyRefresh(
            new ProofKeyRepository($settings),
            $settings,
            function () use ($failing): ?ProofKeys {
                $this->fetches++;
                if ($failing) {
                    throw new \RuntimeException('discovery not reachable');
                }
                return $this->discovered;
            },
            fn(): int => $this->now
        );
    }

    public function testFetchedKeysAreStoredAndReturned(): void
    {
        $keys = ($this->refresh())();

        $this->assertSame('bmV3', $keys->current->modulus);
        $this->assertSame('bmV3', $this->stored['wopi_proof_modulus']);
    }

    public function testFetchesAtMostOnceWithinTheInterval(): void
    {
        $refresh = $this->refresh();

        $this->assertNotNull($refresh());
        $this->now += ProofKeyRefresh::MIN_INTERVAL - 1;
        $this->assertNull($refresh());
        $this->now += 1;
        $this->assertNotNull($refresh());
        $this->assertSame(2, $this->fetches);
    }

    public function testFailedFetchKeepsTheStoredKeys(): void
    {
        $this->stored['wopi_proof_modulus'] = 'b2xk';
        $this->stored['wopi_proof_exponent'] = 'AQAB';

        $this->assertNull(($this->refresh(true))());
        $this->assertSame('b2xk', $this->stored['wopi_proof_modulus']);
    }

    public function testDiscoveryWithoutKeysKeepsTheStoredKeys(): void
    {
        $this->stored['wopi_proof_modulus'] = 'b2xk';
        $this->stored['wopi_proof_exponent'] = 'AQAB';
        $this->discovered = null;

        $this->assertNull(($this->refresh())());
        $this->assertSame('b2xk', $this->stored['wopi_proof_modulus']);
    }
}
