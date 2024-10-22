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

namespace ILIAS\DataProtection\test;

use ilCtrl;
use ILIAS\Refinery\ByTrying;
use ilSetting;
use ilObjectDataCache;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\LazyProvide;
use ILIAS\LegalDocuments\UseSlot;
use ILIAS\DataProtection\Consumer;

require_once __DIR__ . '/bootstrap.php';

class ConsumerTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Consumer::class, new Consumer($this->mock(Container::class)));
    }

    public function testId(): void
    {
        $this->assertSame(Consumer::ID, (new Consumer($this->mock(Container::class)))->id());
    }

    public function testDisabledUses(): void
    {
        $by_trying = $this->mockTree(ByTrying::class, ['transform' => false]);
        $settings = $this->mockMethod(ilSetting::class, 'get', ['dpro_enabled', ''], 'false');

        $container = $this->mockTree(Container::class, [
            'settings' => $settings,
            'refinery' => ['byTrying' => $by_trying],
        ]);
        $container->method('offsetGet')->with('ilObjDataCache')->willReturn($this->mock(ilObjectDataCache::class));

        $slot = $this->mock(UseSlot::class);
        $slot->expects(self::once())->method('hasDocuments')->willReturn($slot);
        $slot->expects(self::once())->method('hasHistory')->willReturn($slot);
        $slot->expects(self::once())->method('hasPublicApi')->willReturn($slot);
        $slot->expects(self::once())->method('hasPublicPage')->willReturn($slot);

        $instance = new Consumer($container);

        $this->assertSame($slot, $instance->uses($slot, $this->mock(LazyProvide::class)));
    }

    public function testUsesWithoutAcceptance(): void
    {
        $by_trying = $this->mockTree(ByTrying::class, ['transform' => true]);
        $settings = $this->mock(ilSetting::class);
        $settings->method('get')->withConsecutive(['dpro_enabled', ''], ['dpro_no_acceptance', ''])->willReturn('true');

        $container = $this->mockTree(Container::class, [
            'settings' => $settings,
            'refinery' => ['byTrying' => $by_trying],
            'ctrl' => $this->mock(ilCtrl::class),
        ]);
        $container->method('offsetGet')->with('ilObjDataCache')->willReturn($this->mock(ilObjectDataCache::class));

        $slot = $this->mock(UseSlot::class);
        $slot->expects(self::once())->method('hasDocuments')->willReturn($slot);
        $slot->expects(self::once())->method('hasHistory')->willReturn($slot);
        $slot->expects(self::once())->method('showOnLoginPage')->willReturn($slot);
        $slot->expects(self::once())->method('showInFooter')->willReturn($slot);
        $slot->expects(self::once())->method('hasPublicPage')->willReturn($slot);
        $slot->expects(self::once())->method('hasPublicApi')->willReturn($slot);

        $instance = new Consumer($container);

        $this->assertSame($slot, $instance->uses($slot, $this->mock(LazyProvide::class)));
    }

    public function testUses(): void
    {
        $by_trying = $this->mock(ByTrying::class);
        $by_trying->method('transform')->willReturnOnConsecutiveCalls(true, false);

        $settings = $this->mock(ilSetting::class);
        $settings->method('get')->withConsecutive(['dpro_enabled', ''], ['dpro_no_acceptance', ''])->willReturnOnConsecutiveCalls('true', 'false');

        $container = $this->mockTree(Container::class, [
            'settings' => $settings,
            'refinery' => ['byTrying' => $by_trying],
            'ctrl' => $this->mock(ilCtrl::class),
        ]);
        $container->method('offsetGet')->with('ilObjDataCache')->willReturn($this->mock(ilObjectDataCache::class));

        $slot = $this->mock(UseSlot::class);
        $slot->expects(self::once())->method('hasDocuments')->willReturn($slot);
        $slot->expects(self::once())->method('hasHistory')->willReturn($slot);
        $slot->expects(self::once())->method('showOnLoginPage')->willReturn($slot);
        $slot->expects(self::once())->method('canWithdraw')->willReturn($slot);
        $slot->expects(self::once())->method('hasAgreement')->willReturn($slot);
        $slot->expects(self::once())->method('showInFooter')->willReturn($slot);
        $slot->expects(self::once())->method('onSelfRegistration')->willReturn($slot);
        $slot->expects(self::once())->method('hasOnlineStatusFilter')->willReturn($slot);
        $slot->expects(self::once())->method('hasUserManagementFields')->willReturn($slot);
        $slot->expects(self::once())->method('hasPublicApi')->willReturn($slot);
        $slot->expects(self::once())->method('canReadInternalMails')->willReturn($slot);
        $slot->expects(self::once())->method('canUseSoapApi')->willReturn($slot);

        $instance = new Consumer($container);

        $this->assertSame($slot, $instance->uses($slot, $this->mock(LazyProvide::class)));
    }
}
