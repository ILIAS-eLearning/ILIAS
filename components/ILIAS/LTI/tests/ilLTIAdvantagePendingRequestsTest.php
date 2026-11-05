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

use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePendingRequestsTest extends TestCase
{
    private const string KIND = 'lti_test_pending';
    private const string SESSION = 'session1';

    private ilLTIAdvantagePendingRequestStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new class () implements ilLTIAdvantagePendingRequestStorage {
            /** @var array<string, array{0: array, 1: int}> */
            public array $rows = [];

            public function read(string $session, string $kind, string $key): ?array
            {
                return $this->rows[$session . '|' . $kind . '|' . $key] ?? null;
            }

            public function write(string $session, string $kind, string $key, array $request, int $created): void
            {
                $this->rows[$session . '|' . $kind . '|' . $key] = [$request, $created];
            }

            public function delete(string $session, string $kind, string $key): bool
            {
                $exists = isset($this->rows[$session . '|' . $kind . '|' . $key]);
                unset($this->rows[$session . '|' . $kind . '|' . $key]);

                return $exists;
            }

            public function deleteOlderThan(string $kind, int $created): void
            {
                foreach ($this->rows as $id => $row) {
                    if (explode('|', $id)[1] === $kind && $row[1] < $created) {
                        unset($this->rows[$id]);
                    }
                }
            }
        };
    }

    private function requests(string $kind = self::KIND, string $session = self::SESSION): ilLTIAdvantagePendingRequests
    {
        return new ilLTIAdvantagePendingRequests($kind, 600, $this->storage, $session);
    }

    private function keep(string $key, array $request, int $created, string $session = self::SESSION): void
    {
        $this->storage->write(hash('sha256', $session), self::KIND, hash('sha256', $key), $request, $created);
    }

    public function testAddedRequestIsWaiting(): void
    {
        $requests = $this->requests();
        $requests->add('state1', ['nonce' => 'n1', 'ref_id' => 42]);

        $request = $requests->get('state1');
        $this->assertSame('n1', $request['nonce']);
        $this->assertSame(42, $request['ref_id']);
        $this->assertArrayHasKey('created', $request);
    }

    public function testUnknownRequestIsNotWaiting(): void
    {
        $requests = $this->requests();
        $requests->add('state1', ['nonce' => 'n1']);

        $this->assertNull($requests->get('state2'));
    }

    public function testTakenRequestIsNoLongerWaiting(): void
    {
        $requests = $this->requests();
        $requests->add('state1', ['nonce' => 'n1']);
        $requests->add('state2', ['nonce' => 'n2']);

        $this->assertSame('n1', $requests->take('state1')['nonce']);
        $this->assertNull($requests->get('state1'));
        $this->assertNull($requests->take('state1'));
        $this->assertSame('n2', $requests->get('state2')['nonce']);
    }

    public function testExpiredRequestIsGone(): void
    {
        $this->keep('old', ['nonce' => 'n1'], time() - 601);
        $this->keep('new', ['nonce' => 'n2'], time() - 10);
        $requests = $this->requests();

        $this->assertNull($requests->get('old'));
        $this->assertNull($requests->take('old'));
        $this->assertSame('n2', $requests->get('new')['nonce']);
    }

    public function testExpiredRequestsOfAllSessionsAreRemovedWhenOneIsAdded(): void
    {
        $this->keep('old', ['nonce' => 'n1'], time() - 601);
        $this->keep('other', ['nonce' => 'n3'], time() - 601, 'session2');
        $this->requests()->add('new', ['nonce' => 'n2']);

        $this->assertCount(1, $this->storage->rows);
        $this->assertSame('n2', $this->requests()->get('new')['nonce']);
    }

    public function testKindsKeepRequestsApart(): void
    {
        $logins = $this->requests(self::KIND . '_login');
        $deep_links = $this->requests(self::KIND . '_deep_linking');
        $logins->add('state1', ['nonce' => 'n1']);

        $this->assertNull($deep_links->get('state1'));
        $this->assertSame('n1', $logins->get('state1')['nonce']);
    }

    public function testSessionsKeepRequestsApart(): void
    {
        $this->requests()->add('state1', ['nonce' => 'n1']);

        $this->assertNull($this->requests(self::KIND, 'session2')->get('state1'));
        $this->assertNull($this->requests(self::KIND, 'session2')->take('state1'));
        $this->assertSame('n1', $this->requests()->get('state1')['nonce']);
    }

    public function testRequestsOfTheSameSessionDoNotReplaceEachOther(): void
    {
        // two requests of one session, each with its own instance as in two parallel requests of ILIAS
        $this->requests()->add('launch1', ['nonce' => 'n1']);
        $this->requests()->add('launch2', ['nonce' => 'n2']);

        $this->assertSame('n1', $this->requests()->take('launch1')['nonce']);
        $this->assertSame('n2', $this->requests()->take('launch2')['nonce']);
    }

    public function testNothingIsKeptWithoutSession(): void
    {
        $requests = $this->requests(self::KIND, '');
        $requests->add('state1', ['nonce' => 'n1']);

        $this->assertSame([], $this->storage->rows);
        $this->assertNull($requests->get('state1'));
        $this->assertNull($requests->take('state1'));
    }
}
