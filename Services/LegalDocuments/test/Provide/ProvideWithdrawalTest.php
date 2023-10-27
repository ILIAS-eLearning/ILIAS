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

namespace ILIAS\LegalDocuments\test\Provide;

use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Provide\ProvideWithdrawal;
use ilCtrl;
use ilAuthSession;
use ilSession;

require_once __DIR__ . '/../ContainerMock.php';

class ProvideWithdrawalTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ProvideWithdrawal::class, new ProvideWithdrawal(
            'foo',
            $this->mock(ilCtrl::class),
            $this->mock(ilAuthSession::class)
        ));
    }

    public function testBeginProcessURL(): void
    {
        $instance = new ProvideWithdrawal(
            'foo',
            $this->mock(ilCtrl::class),
            $this->mock(ilAuthSession::class),
            function (array $params): string {
                $this->assertSame(['withdraw_consent' => 'foo'], $params);
                return 'logout url';
            }
        );

        $this->assertSame('logout url', $instance->beginProcessURL());
    }

    public function testFinishAndLogout(): void
    {
        $called = false;

        $auth_session = $this->mock(ilAuthSession::class);
        $auth_session->expects(self::once())->method('logout');

        $ctrl = $this->mock(ilCtrl::class);
        $ctrl->expects(self::once())->method('redirectToURL')->with('login.php?bar=baz&withdrawal_finished=foo&cmd=force_login');

        $instance = new ProvideWithdrawal(
            'foo',
            $ctrl,
            $auth_session,
            $this->fail(...),
            function (int $x) use (&$called) {
                $this->assertSame(ilSession::SESSION_CLOSE_USER, $x);
                $called = true;
            }
        );

        $instance->finishAndLogout(['bar' => 'baz']);

        $this->assertTrue($called);
    }
}
