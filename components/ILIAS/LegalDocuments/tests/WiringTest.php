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
use ILIAS\LegalDocuments\Map;
use ILIAS\LegalDocuments\SlotConstructor;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Wiring;
use ILIAS\LegalDocuments\SelectionMap;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/ContainerMock.php';

class WiringTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Wiring::class, new Wiring($this->stub(SlotConstructor::class), $this->stub(Map::class)));
    }

    public function testAfterLogin(): void
    {
        $proc = $this->fail(...);
        $map = $this->mapExpecting('add', ['after-login', $proc]);

        $instance = new Wiring($this->stub(SlotConstructor::class), $map);
        $this->assertSame($map, $instance->afterLogin($proc)->map());
    }

    public function testShowInFooter(): void
    {
        $proc = $this->fail(...);
        $map = $this->mapExpecting('set', ['footer', 'foo', $proc]);

        $instance = new Wiring($this->stubTree(SlotConstructor::class, ['id' => 'foo']), $map);
        $this->assertSame($map, $instance->showInFooter($proc)->map());
    }

    public function testCanWithdraw(): void
    {
        $withdraw_process = $this->stub(WithdrawProcess::class);

        $instance = new Wiring($this->stub(SlotConstructor::class), new Map());

        $map = $instance->canWithdraw($withdraw_process)->map()->value();

        $this->assertSame([
            'withdraw',
            'intercept',
            'logout-text',
            'show-on-login-page',
        ], array_keys($map));

        $this->assertTrue(array_is_list($map['intercept']));
        $this->assertTrue(array_is_list($map['show-on-login-page']));
        $this->assertFalse(array_is_list($map['withdraw']));
        $this->assertFalse(array_is_list($map['logout-text']));
    }

    public function testShowOnLoginPage(): void
    {
        $proc = $this->fail(...);
        $instance = new Wiring($this->stubTree(SlotConstructor::class, ['id' => 'foo']), new Map());
        $this->assertSame(['show-on-login-page' => ['foo' => $proc]], $instance->showOnLoginPage($proc)->map()->value());
    }

    public function testHasAgreement(): void
    {
        $instance = new Wiring($this->stub(SlotConstructor::class), new Map());
        $map = $instance->hasAgreement($this->stub(Agreement::class), 'bar')->map()->value();

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
        $document = $this->stub(ProvideDocument::class);
        $history = $this->stub(ProvideHistory::class);
        $slot = $this->mockMethod(SlotConstructor::class, 'history', [$document], $history);
        $slot->method('id')->willReturn('foo');
        $map = $this->mockTree(Map::class, ['value' => ['document' => ['foo' => $document]]]);
        $map->expects($this->once())->method('set')->with('history', 'foo', $history)->willReturn($map);

        $instance = new Wiring($slot, $map);
        $this->assertSame($map, $instance->hasHistory()->map());
    }

    public function testOnSelfRegistration(): void
    {
        $self_registration = $this->stub(SelfRegistration::class);
        $map = $this->mapExpecting('add', ['self-registration', $self_registration]);

        $instance = new Wiring($this->stub(SlotConstructor::class), $map);
        $this->assertSame($map, $instance->onSelfRegistration($self_registration)->map());
    }

    public function testHasOnlineStatusFilter(): void
    {
        $proc = $this->fail(...);
        $map = $this->mapExpecting('add', ['filter-online-users', $proc]);

        $instance = new Wiring($this->stub(SlotConstructor::class), $map);
        $this->assertSame($map, $instance->hasOnlineStatusFilter($proc)->map());
    }

    public function testCanReadInternalMails(): void
    {
        $constraint = $this->stub(Constraint::class);
        $map = $this->mapExpecting('add', ['constrain-internal-mail', $constraint]);

        $instance = new Wiring($this->stub(SlotConstructor::class), $map);
        $this->assertSame($map, $instance->canReadInternalMails($constraint)->map());
    }

    public function testCanUseSoapApi(): void
    {
        $constraint = $this->stub(Constraint::class);
        $map = $this->mapExpecting('add', ['use-soap-api', $constraint]);

        $instance = new Wiring($this->stub(SlotConstructor::class), $map);
        $this->assertSame($map, $instance->canUseSoapApi($constraint)->map());
    }

    public function testHasDocuments(): void
    {
        $instance = new Wiring($this->stub(SlotConstructor::class), new Map());
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
        $proc = $this->fail(...);
        $map = $this->mapExpecting('set', ['user-management-fields', 'foo', $proc]);

        $instance = new Wiring(
            $this->stubTree(SlotConstructor::class, ['id' => 'foo']),
            $map
        );

        $this->assertSame($map, $instance->hasUserManagementFields($proc)->map());
    }

    public function testHasPublicApi(): void
    {
        $public_api = $this->stub(PublicApi::class);
        $map = $this->mapExpecting('set', ['public-api', 'foo', $public_api]);

        $instance = new Wiring(
            $this->stubTree(SlotConstructor::class, ['id' => 'foo']),
            $map
        );

        $this->assertSame($map, $instance->hasPublicApi($public_api)->map());
    }

    public function testHasPublicPage(): void
    {
        $public_page = fn() => null;
        $map = $this->mapExpecting('set', ['public-page', 'foo']);

        $instance = new Wiring(
            $this->stubTree(SlotConstructor::class, ['id' => 'foo']),
            $map
        );

        $this->assertSame($map, $instance->hasPublicPage($public_page)->map());
    }

    public function testHasPublicPageWithGotoLink(): void
    {
        $public_page = fn() => null;
        $map = $this->mock(Map::class);
        $map->expects($this->once())->method('add')->with('goto')->willReturnSelf();

        $instance = new Wiring(
            $this->stubTree(SlotConstructor::class, ['id' => 'foo']),
            $this->mockMethod(Map::class, 'set', ['public-page', 'foo'], $map)
        );

        $this->assertSame($map, $instance->hasPublicPage($public_page, 'foo')->map());
    }

    public function testMap(): void
    {
        $map = $this->stub(Map::class);
        $this->assertSame($map, (new Wiring($this->stub(SlotConstructor::class), $map))->map());
    }

    /**
     * @param list<mixed> $arguments
     */
    private function mapExpecting(string $method, array $arguments): Map&MockObject
    {
        $map = $this->mock(Map::class);
        $map->expects($this->once())->method($method)->with(...$arguments)->willReturnSelf();

        return $map;
    }
}
