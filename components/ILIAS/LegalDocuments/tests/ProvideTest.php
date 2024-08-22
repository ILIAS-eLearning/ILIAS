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
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\Provide\ProvidePublicPage;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Provide\ProvideWithdrawal;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Internal;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Provide;
use ilCtrl;
use ilAuthSession;

require_once __DIR__ . '/ContainerMock.php';

class ProvideTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Provide::class, new Provide('foo', $this->mock(Internal::class), $this->mock(Container::class)));
    }

    public function testWithdrawal(): void
    {
        $container = $this->mockTree(Container::class, ['ctrl' => $this->mock(ilCtrl::class)]);
        $container->expects(self::once())->method('offsetGet')->with('ilAuthSession')->willReturn($this->mock(ilAuthSession::class));

        $instance = new Provide('foo', $this->mockMethod(Internal::class, 'get', ['withdraw'], 'foo'), $container);

        $this->assertInstanceOf(ProvideWithdrawal::class, $instance->withdrawal());
    }

    public function testPublicPage(): void
    {
        $container = $this->mockTree(Container::class, ['ctrl' => $this->mock(ilCtrl::class)]);

        $this->assertInstanceOf(ProvidePublicPage::class, (new Provide('foo', $this->mockMethod(
            Internal::class,
            'get',
            ['public-page', 'foo'],
            true
        ), $container))->publicPage());
    }

    public function testDocument(): void
    {
        $document = $this->mock(ProvideDocument::class);
        $internal = $this->mockMethod(Internal::class, 'get', ['document', 'foo'], $document);

        $instance = new Provide('foo', $internal, $this->mock(Container::class));
        $this->assertSame($document, $instance->document());
    }

    public function testHistory(): void
    {
        $history = $this->mock(ProvideHistory::class);
        $internal = $this->mockMethod(Internal::class, 'get', ['history', 'foo'], $history);

        $instance = new Provide('foo', $internal, $this->mock(Container::class));
        $this->assertSame($history, $instance->history());
    }

    public function testAllowEditing(): void
    {
        $document = $this->mock(ProvideDocument::class);

        $internal = $this->mock(Internal::class);
        $consecutive = [
            ['document', 'foo'],
            ['writable-document', 'foo']
        ];
        $internal
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(
                function ($a, $b) use (&$consecutive, $document) {
                    [$ea, $eb] = array_shift($consecutive);
                    $this->assertEquals($ea, $a);
                    $this->assertEquals($eb, $b);
                    return $document;
                }
            );

        $instance = new Provide('foo', $internal, $this->mock(Container::class));
        $instance->document();
        $instance->allowEditing()->document();
    }

    public function testPublicApi(): void
    {
        $public_api = $this->mock(PublicApi::class);
        $internal = $this->mockMethod(Internal::class, 'get', ['public-api', 'foo'], $public_api);

        $instance = new Provide('foo', $internal, $this->mock(Container::class));

        $this->assertSame($public_api, $instance->publicApi());
    }

    public function testId(): void
    {
        $this->assertSame('foo', (new Provide('foo', $this->mock(Internal::class), $this->mock(Container::class)))->id());
    }
}
