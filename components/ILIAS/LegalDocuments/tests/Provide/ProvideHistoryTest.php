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

use ILIAS\Data\Result;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\LegalDocuments\Legacy\Table as LegacyTable;
use ILIAS\LegalDocuments\Table as TableInterface;
use ILIAS\LegalDocuments\Table\HistoryTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\Repository\HistoryRepository;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ilObjUser;
use ilGlobalTemplateInterface;
use ilLanguage;
use stdClass;
use ilCtrl;

require_once __DIR__ . '/../ContainerMock.php';

class ProvideHistoryTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ProvideHistory::class, new ProvideHistory('foo', $this->mock(HistoryRepository::class), $this->mock(ProvideDocument::class), $this->mock(Container::class)));
    }

    public function testTable(): void
    {
        $dummy_gui = new stdClass();
        $legacy = $this->mock(Legacy::class);

        $table = $this->mockMethod(LegacyTable::class, 'getHTML', [], 'table html');

        $container = $this->mockTree(Container::class, [
            'ctrl' => $this->mock(ilCtrl::class),
            'language' => $this->mock(ilLanguage::class),
            'ui' => [
                'factory' => $this->mockMethod(UIFactory::class, 'legacy', ['table html'], $legacy),
                'mainTemplate' => $this->mock(ilGlobalTemplateInterface::class)
            ],
        ]);

        $create_table_gui = function (object $gui, string $command, TableInterface $t) use ($dummy_gui, $table): LegacyTable {
            $this->assertSame($dummy_gui, $gui);
            $this->assertSame('dummy command', $command);
            $this->assertInstanceOf(HistoryTable::class, $t);
            return $table;
        };

        $instance = new ProvideHistory('foo', $this->mock(HistoryRepository::class), $this->mock(ProvideDocument::class), $container, $create_table_gui);

        $this->assertSame($legacy, $instance->table($dummy_gui, 'dummy command', 'reset command', 'auto complete command'));
    }

    public function testAcceptDocument(): void
    {
        $user = $this->mock(ilObjUser::class);
        $document = $this->mock(Document::class);

        $repository = $this->mock(HistoryRepository::class);
        $repository->expects(self::once())->method('acceptDocument')->with($user, $document);

        $instance = new ProvideHistory('foo', $repository, $this->mock(ProvideDocument::class), $this->mock(Container::class));
        $instance->acceptDocument($user, $document);
    }

    public function testAlreadyAccepted(): void
    {
        $user = $this->mock(ilObjUser::class);
        $document = $this->mock(Document::class);

        $repository = $this->mockMethod(HistoryRepository::class, 'alreadyAccepted', [$user, $document], true);

        $instance = new ProvideHistory('foo', $repository, $this->mock(ProvideDocument::class), $this->mock(Container::class));
        $this->assertTrue($instance->alreadyAccepted($user, $document));
    }

    public function testAcceptedVersion(): void
    {
        $user = $this->mock(ilObjUser::class);

        $result = $this->mock(Result::class);

        $repository = $this->mockMethod(HistoryRepository::class, 'acceptedVersion', [$user], $result);

        $instance = new ProvideHistory('foo', $repository, $this->mock(ProvideDocument::class), $this->mock(Container::class));
        $this->assertSame($result, $instance->acceptedVersion($user));
    }

    public function testCurrentDocumentOfAcceptedVersion(): void
    {
        $user = $this->mock(ilObjUser::class);
        $result = $this->mock(Result::class);
        $repository = $this->mockMethod(HistoryRepository::class, 'currentDocumentOfAcceptedVersion', [$user], $result);
        $instance = new ProvideHistory('foo', $repository, $this->mock(ProvideDocument::class), $this->mock(Container::class));

        $this->assertSame($result, $instance->currentDocumentOfAcceptedVersion($user));
    }
}
