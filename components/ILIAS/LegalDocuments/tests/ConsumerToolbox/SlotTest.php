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

use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\OnlineStatusFilter;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\ModifyFooter;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\LegalDocuments\ConsumerSlots\Agreement;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\WithdrawProcess;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\ShowOnLoginPage;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\LazyProvide;
use ILIAS\LegalDocuments\ConsumerToolbox\Blocks;
use ILIAS\LegalDocuments\ConsumerToolbox\Slot;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../ContainerMock.php';

class SlotTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Slot::class, new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        ));
    }

    public function testShowOnLoginPage(): void
    {
        $instance = new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        );

        $this->assertInstanceOf(ShowOnLoginPage::class, $instance->showOnLoginPage());
    }

    public function testWithdrawProcess(): void
    {
        $instance = new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        );

        $this->assertInstanceOf(WithdrawProcess::class, $instance->withdrawProcess($this->mock(User::class), $this->mock(Settings::class), $this->fail(...)));
    }

    public function testAgreement(): void
    {
        $instance = new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        );
        $this->assertInstanceOf(Agreement::class, $instance->agreement($this->mock(User::class), $this->mock(Settings::class)));
    }

    public function testModifyFooter(): void
    {
        $instance = new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        );

        $this->assertInstanceOf(ModifyFooter::class, $instance->modifyFooter($this->mock(User::class)));
    }

    public function testSelfRegistration(): void
    {
        $instance = new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        );

        $this->assertInstanceOf(SelfRegistration::class, $instance->selfRegistration($this->mock(User::class), $this->fail(...)));
    }

    public function testOnlineStatusFilter(): void
    {
        $instance = $instance = new Slot(
            'foo',
            $this->mock(Blocks::class),
            $this->mock(LazyProvide::class),
            $this->mock(Container::class)
        );

        $this->assertInstanceOf(OnlineStatusFilter::class, $instance->onlineStatusFilter($this->fail(...)));
    }
}
