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
    private const string SESSION_KEY = 'lti_test_pending';

    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testAddedRequestIsWaiting(): void
    {
        $requests = new ilLTIAdvantagePendingRequests(self::SESSION_KEY, 600);
        $requests->add('state1', ['nonce' => 'n1', 'ref_id' => 42]);

        $request = $requests->get('state1');
        $this->assertSame('n1', $request['nonce']);
        $this->assertSame(42, $request['ref_id']);
        $this->assertArrayHasKey('created', $request);
    }

    public function testUnknownRequestIsNotWaiting(): void
    {
        $requests = new ilLTIAdvantagePendingRequests(self::SESSION_KEY, 600);
        $requests->add('state1', ['nonce' => 'n1']);

        $this->assertNull($requests->get('state2'));
    }

    public function testTakenRequestIsNoLongerWaiting(): void
    {
        $requests = new ilLTIAdvantagePendingRequests(self::SESSION_KEY, 600);
        $requests->add('state1', ['nonce' => 'n1']);
        $requests->add('state2', ['nonce' => 'n2']);

        $this->assertSame('n1', $requests->take('state1')['nonce']);
        $this->assertNull($requests->get('state1'));
        $this->assertNull($requests->take('state1'));
        $this->assertSame('n2', $requests->get('state2')['nonce']);
    }

    public function testExpiredRequestIsGone(): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'old' => ['created' => time() - 601, 'nonce' => 'n1'],
            'new' => ['created' => time() - 10, 'nonce' => 'n2'],
        ];
        $requests = new ilLTIAdvantagePendingRequests(self::SESSION_KEY, 600);

        $this->assertNull($requests->get('old'));
        $this->assertNull($requests->take('old'));
        $this->assertSame('n2', $requests->get('new')['nonce']);
    }

    public function testExpiredRequestsAreRemovedFromTheSession(): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'old' => ['created' => time() - 601, 'nonce' => 'n1'],
        ];
        $requests = new ilLTIAdvantagePendingRequests(self::SESSION_KEY, 600);
        $requests->add('new', ['nonce' => 'n2']);

        $this->assertSame(['new'], array_keys($_SESSION[self::SESSION_KEY]));
    }

    public function testMalformedSessionDataIsIgnored(): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'string' => 'not a request',
            'undated' => ['nonce' => 'n1'],
        ];
        $requests = new ilLTIAdvantagePendingRequests(self::SESSION_KEY, 600);

        $this->assertNull($requests->get('string'));
        $this->assertNull($requests->get('undated'));

        $_SESSION[self::SESSION_KEY] = 'not a list';
        $this->assertNull($requests->get('string'));
    }

    public function testSessionKeysKeepRequestsApart(): void
    {
        $logins = new ilLTIAdvantagePendingRequests(self::SESSION_KEY . '_login', 600);
        $deep_links = new ilLTIAdvantagePendingRequests(self::SESSION_KEY . '_deep_linking', 600);
        $logins->add('state1', ['nonce' => 'n1']);

        $this->assertNull($deep_links->get('state1'));
        $this->assertSame('n1', $logins->get('state1')['nonce']);
    }
}
