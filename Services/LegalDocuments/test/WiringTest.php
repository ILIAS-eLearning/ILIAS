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

namespace ILIAS\LegalDocuments\test;

use ILIAS\LegalDocuments\ConsumerSlots\PublicApi;
use ILIAS\Refinery\Constraint;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\ConsumerSlots\Agreement;
use ILIAS\LegalDocuments\ConsumerSlots\WithdrawProcess;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Map;
use ILIAS\LegalDocuments\SlotConstructor;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Wiring;
use ILIAS\LegalDocuments\SelectionMap;

require_once __DIR__ . '/ContainerMock.php';

class WiringTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Wiring::class, new Wiring($this->mock(SlotConstructor::class), $this->mock(Map::class)));
    }

    public function testAfterLogin(): void
    {
        $map = $this->mock(Map::class);
        $proc = $this->fail(...);

        $instance = new Wiring($this->mock(SlotConstructor::class), $this->mockMethod(Map::class, 'add', ['after-login', $proc], $map));
        $this->assertSame($map, $instance->afterLogin($proc)->map());
    }

    public function testShowInFooter(): void
    {
        $map = $this->mock(Map::class);
        $proc = $this->fail(...);

        $instance = new Wiring($this->mockTree(SlotConstructor::class, ['id' => 'foo']), $this->mockMethod(Map::class, 'set', ['footer', 'foo', $proc], $map));
        $this->assertSame($map, $instance->showInFooter($proc)->map());
    }

    public function testCanWithdraw(): void
    {
        $withdraw_process = $this->mock(WithdrawProcess::class);

        $instance = new Wiring($this->mock(SlotConstructor::class), new Map());

        $map = $instance->canWithdraw($withdraw_process)->map()->value();

        $this->assertSame([
            'withdraw',
            'logout',
            'intercept',
            'logout-text',
            'show-on-login-page',
        ], array_keys($map));

        $this->assertTrue(array_is_list($map['intercept']));
        $this->assertTrue(array_is_list($map['show-on-login-page']));
        $this->assertFalse(array_is_list($map['withdraw']));
        $this->assertFalse(array_is_list($map['logout']));
        $this->assertFalse(array_is_list($map['logout-text']));
    }

    public function testShowOnLoginPage(): void
    {
        $proc = $this->fail(...);
        $instance = new Wiring($this->mockTree(SlotConstructor::class, ['id' => 'foo']), new Map());
        $this->assertSame(['show-on-login-page' => ['foo' => $proc]], $instance->showOnLoginPage($proc)->map()->value());
    }

    public function testHasAgreement(): void
    {
        $instance = new Wiring($this->mock(SlotConstructor::class), new Map());
        $map = $instance->hasAgreement($this->mock(Agreement::class), 'bar')->map()->value();

        $this->assertSame([
            'public-page',
            'agreement-form',
            'intercept',
            'goto',
        ], array_keys($map));

        $this->assertFalse(array_is_list($map['public-page']));
        $this->assertFalse(array_is_list($map['agreement-form']));
        $this->assertTrue(array_is_list($map['intercept']));
        $this->assertTrue(array_is_list($map['goto']));
    }

    public function testHasHistory(): void
    {
        $document = $this->mock(ProvideDocument::class);
        $history = $this->mock(ProvideHistory::class);
        $slot = $this->mockMethod(SlotConstructor::class, 'history', [$document], $history);
        $slot->method('id')->willReturn('foo');
        $map = $this->mockTree(Map::class, ['value' => ['document' => ['foo' => $document]]]);
        $map->expects(self::once())->method('set')->with('history', 'foo', $history)->willReturn($map);

        $instance = new Wiring($slot, $map);
        $this->assertSame($map, $instance->hasHistory()->map());
    }

    public function testOnSelfRegistration(): void
    {
        $map = $this->mock(Map::class);
        $self_registration = $this->mock(SelfRegistration::class);

        $instance = new Wiring($this->mock(SlotConstructor::class), $this->mockMethod(Map::class, 'add', ['self-registration', $self_registration], $map));
        $this->assertSame($map, $instance->onSelfRegistration($self_registration)->map());
    }

    public function testHasOnlineStatusFilter(): void
    {
        $map = $this->mock(Map::class);
        $proc = $this->fail(...);

        $instance = new Wiring($this->mock(SlotConstructor::class), $this->mockMethod(Map::class, 'add', ['filter-online-users', $proc], $map));
        $this->assertSame($map, $instance->hasOnlineStatusFilter($proc)->map());
    }

    public function testCanReadInternalMails(): void
    {
        $map = $this->mock(Map::class);
        $constraint = $this->mock(Constraint::class);

        $instance = new Wiring($this->mock(SlotConstructor::class), $this->mockMethod(Map::class, 'add', ['constrain-internal-mail', $constraint], $map));
        $this->assertSame($map, $instance->canReadInternalMails($constraint)->map());
    }

    public function testCanUseSoapApi(): void
    {
        $map = $this->mock(Map::class);
        $constraint = $this->mock(Constraint::class);

        $instance = new Wiring($this->mock(SlotConstructor::class), $this->mockMethod(Map::class, 'add', ['use-soap-api', $constraint], $map));
        $this->assertSame($map, $instance->canUseSoapApi($constraint)->map());
    }

    public function testHasDocuments(): void
    {
        $instance = new Wiring($this->mock(SlotConstructor::class), new Map());
        $map = $instance->hasDocuments([], new SelectionMap())->map()->value();

        $this->assertSame([
            'document',
            'writable-document',
        ], array_keys($map));

        $this->assertFalse(array_is_list($map['document']));
        $this->assertFalse(array_is_list($map['writable-document']));
    }

    public function testHasUserManagementFields(): void
    {
        $map = $this->mock(Map::class);
        $proc = $this->fail(...);

        $instance = new Wiring(
            $this->mockTree(SlotConstructor::class, ['id' => 'foo']),
            $this->mockMethod(Map::class, 'set', ['user-management-fields', 'foo', $proc], $map)
        );

        $this->assertSame($map, $instance->hasUserManagementFields($proc)->map());
    }

    public function testHasPublicApi(): void
    {
        $map = $this->mock(Map::class);
        $public_api = $this->mock(PublicApi::class);

        $instance = new Wiring(
            $this->mockTree(SlotConstructor::class, ['id' => 'foo']),
            $this->mockMethod(Map::class, 'set', ['public-api', 'foo', $public_api], $map)
        );

        $this->assertSame($map, $instance->hasPublicApi($public_api)->map());
    }

    public function testHasPublicPage(): void
    {
        $map = $this->mock(Map::class);
        $public_page = fn() => null;

        $instance = new Wiring(
            $this->mockTree(SlotConstructor::class, ['id' => 'foo']),
            $this->mockMethod(Map::class, 'set', ['public-page', 'foo', $public_page], $map)
        );

        $this->assertSame($map, $instance->hasPublicPage($public_page)->map());
    }

    public function testHasPublicPageWithGotoLink(): void
    {
        $m = $this->mock(Map::class);
        $map = $this->mockMethod(Map::class, 'add', ['goto'], $m);
        $public_page = fn() => null;

        $instance = new Wiring(
            $this->mockTree(SlotConstructor::class, ['id' => 'foo']),
            $this->mockMethod(Map::class, 'set', ['public-page', 'foo', $public_page], $map)
        );

        $this->assertSame($m, $instance->hasPublicPage($public_page, 'foo')->map());
    }

    public function testMap(): void
    {
        $map = $this->mock(Map::class);
        $this->assertSame($map, (new Wiring($this->mock(SlotConstructor::class), $map))->map());
    }
}
