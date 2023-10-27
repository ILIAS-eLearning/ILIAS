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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox;

use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;
use ilCtrl;

require_once __DIR__ . '/../ContainerMock.php';

class RoutingTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Routing::class, new Routing(
            $this->mock(ilCtrl::class),
            $this->mock(SelectSetting::class),
            $this->fail(...),
            $this->fail(...)
        ));
    }

    public function testCtrl(): void
    {
        $ctrl = $this->mock(ilCtrl::class);
        $this->assertSame($ctrl, (new Routing($ctrl, $this->mock(SelectSetting::class), $this->fail(...), $this->fail(...)))->ctrl());
    }

    public function testLogoutUrl(): void
    {
        $this->assertSame('dummy logout url', (new Routing(
            $this->mock(ilCtrl::class),
            $this->mock(SelectSetting::class),
            $this->fail(...),
            fn() => 'dummy logout url'
        ))->logoutUrl());
    }

    public function testRedirectToStartingPage(): void
    {
        $called = false;

        $session = $this->mock(SelectSetting::class);
        $session->expects(self::once())->method('typed')->willReturnCallback(function (string $key, callable $proc) {
            $this->assertSame('orig_request_target', $key);
            return $this->mockTree(Setting::class, ['value' => null]);
        });

        (new Routing(
            $this->mock(ilCtrl::class),
            $session,
            static function () use (&$called): void {
                $called = true;
            },
            $this->fail(...)
        ))->redirectToOriginalTarget();

        $this->assertTrue($called);
    }

    public function testRedirectToOriginalTarget(): void
    {
        $setting = $this->mock(Setting::class);
        $setting->method('value')->willReturn('some url');
        $setting->expects(self::once())->method('update')->with(null);

        $session = $this->mock(SelectSetting::class);
        $session->method('typed')->willReturnCallback(function (string $key, callable $proc) use ($setting) {
            $this->assertSame('orig_request_target', $key);

            return $setting;
        });

        $ctrl = $this->mock(ilCtrl::class);
        $ctrl->expects(self::once())->method('redirectToURL')->with('some url');

        (new Routing(
            $ctrl,
            $session,
            $this->fail(...),
            $this->fail(...)
        ))->redirectToOriginalTarget();
    }
}
