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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ILIAS\LegalDocuments\PageFragment;
use ilGlobalTemplateInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\WithdrawProcess;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class WithdrawProcessTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(WithdrawProcess::class, new WithdrawProcess(
            $this->mock(User::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            $this->fail(...),
            $this->mock(Provide::class),
            $this->fail(...)
        ));
    }

    public function testShowValidatePasswordMessage(): void
    {
        $instance = new WithdrawProcess(
            $this->mock(User::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            fn() => 'internal',
            $this->mock(Provide::class),
            $this->fail(...)
        );

        $array = $instance->showValidatePasswordMessage();
        $this->assertSame(2, count($array));
        $this->assertInstanceOf(Component::class, $array[0]);
        $this->assertInstanceOf(Component::class, $array[1]);
    }

    public function testIsOnGoing(): void
    {
        $instance = new WithdrawProcess(
            $this->mockTree(User::class, ['withdrawalRequested' => ['value' => true]]),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            $this->fail(...),
            $this->mock(Provide::class),
            $this->fail(...)
        );

        $this->assertTrue($instance->isOnGoing());
    }

    public function testWithdrawalRequested(): void
    {
        $setting = $this->mock(Setting::class);
        $setting->expects(self::once())->method('update')->with(true);

        $instance = new WithdrawProcess(
            $this->mockTree(User::class, ['cannotAgree' => false, 'neverAgreed' => false, 'withdrawalRequested' => $setting]),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            $this->fail(...),
            $this->mock(Provide::class),
            $this->fail(...)
        );

        $instance->withdrawalRequested();
    }

    public function testWithdrawalRequestedWithInvalidUser(): void
    {
        $instance = new WithdrawProcess(
            $this->mockTree(User::class, ['cannotAgree' => true, 'neverAgreed' => false]),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            $this->fail(...),
            $this->mock(Provide::class),
            $this->fail(...)
        );

        $instance->withdrawalRequested();
        $this->assertTrue(true);
    }

    public function testWithdrawalFinished(): void
    {
        $main_template = $this->mock(ilGlobalTemplateInterface::class);
        $main_template->expects(self::once())->method('setOnScreenMessage');

        $instance = new WithdrawProcess(
            $this->mock(User::class),
            $this->mockTree(UI::class, ['mainTemplate' => $main_template]),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            fn() => null,
            $this->mock(Provide::class),
            $this->fail(...)
        );

        $instance->withdrawalFinished();
    }

    public function testShowWithdraw(): void
    {
        $instance = new WithdrawProcess(
            $this->mock(User::class),
            $this->mock(UI::class),
            $this->mock(Routing::class),
            $this->mock(Settings::class),
            $this->fail(...),
            $this->mock(Provide::class),
            $this->fail(...)
        );

        $this->assertInstanceOf(PageFragment::class, $instance->showWithdraw('foo', ''));
    }
}
