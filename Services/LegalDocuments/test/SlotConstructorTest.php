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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\LegalDocuments\Repository\ReadOnlyDocumentRepository;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\LegalDocuments\Repository\DatabaseDocumentRepository;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\UserAction;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\SlotConstructor;

require_once __DIR__ . '/ContainerMock.php';

class SlotConstructorTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(SlotConstructor::class, new SlotConstructor('foo', $this->mock(Container::class), $this->mock(UserAction::class)));
    }

    public function testId(): void
    {
        $this->assertSame('foo', (new SlotConstructor('foo', $this->mock(Container::class), $this->mock(UserAction::class)))->id());
    }

    public function testHistory(): void
    {
        $document = $this->mockTree(ProvideDocument::class, ['repository' => $this->mock(DatabaseDocumentRepository::class)]);
        $instance = new SlotConstructor('foo', $this->mock(Container::class), $this->mock(UserAction::class));
        $this->assertInstanceOf(ProvideHistory::class, $instance->history($document));
    }

    public function testDocument(): void
    {
        $instance = new SlotConstructor('foo', $this->mock(Container::class), $this->mock(UserAction::class));
        $this->assertInstanceOf(ProvideDocument::class, $instance->document($this->mock(DocumentRepository::class), [], []));
    }

    public function testDocumentRepository(): void
    {
        $instance = new SlotConstructor('foo', $this->mock(Container::class), $this->mock(UserAction::class));
        $this->assertInstanceOf(DatabaseDocumentRepository::class, $instance->documentRepository());
    }

    public function testReadOnlyDocuments(): void
    {
        $instance = new SlotConstructor('foo', $this->mock(Container::class), $this->mock(UserAction::class));
        $this->assertInstanceOf(ReadOnlyDocumentRepository::class, $instance->readOnlyDocuments($this->mock(DocumentRepository::class)));
    }

    public function testWithdrawalFinished(): void
    {
        $called = false;

        $container = $this->mockTree(Container::class, [
            'http' => ['request' => $this->mockTree(ServerRequestInterface::class, ['getQueryParams' => ['withdrawal_finished' => 'foo']])]
        ]);

        $instance = new SlotConstructor('foo', $container, $this->mock(UserAction::class));

        $proc = $instance->withdrawalFinished(function () use (&$called): void {
            $called = true;
        });

        $this->assertFalse($called);
        $proc();
        $this->assertTrue($called);
    }

    public function testWithdrawalFinishedWithoutQueryParam(): void
    {
        $called = false;

        $container = $this->mockTree(Container::class, [
            'http' => ['request' => $this->mockTree(ServerRequestInterface::class, ['getQueryParams' => []])]
        ]);

        $instance = new SlotConstructor('foo', $container, $this->mock(UserAction::class));

        $proc = $instance->withdrawalFinished(function () use (&$called): void {
            $called = true;
        });

        $this->assertFalse($called);
        $proc();
        $this->assertFalse($called);
    }
}
