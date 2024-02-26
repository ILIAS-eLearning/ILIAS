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

namespace ILIAS\LegalDocuments\test\Table;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Table\DocumentTable;
use DateTimeImmutable;

require_once __DIR__ . '/../ContainerMock.php';

class DocumentTableTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(DocumentTable::class, new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class)
        ));
    }

    public function testColumns(): void
    {
        $ui = $this->mock(UI::class);
        $ui->method('txt')->willReturnCallback(fn($s) => 'txt: ' . $s);

        $this->assertSame([
            'order' => ['txt: tbl_docs_head_sorting', '', '5%'],
            'title' => ['txt: tbl_docs_head_title', '', '25%'],
            'created' => ['txt: tbl_docs_head_created'],
            'change' => ['txt: tbl_docs_head_last_change'],
            'criteria' => ['txt: tbl_docs_head_criteria'],
        ], (new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $ui,
            $this->mock(DocumentModal::class)
        ))->columns());
    }

    public function testConfig(): void
    {
        $ui = $this->mock(UI::class);
        $ui->method('txt')->willReturnCallback(fn($s) => 'txt: ' . $s);

        $config = $this->mock(TableConfig::class);
        $config->expects(self::once())->method('setTitle')->with('txt: tbl_docs_title');
        $config->expects(self::once())->method('setSelectableColumns')->with('created', 'change');

        $instance = new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $ui,
            $this->mock(DocumentModal::class)
        );

        $instance->config($config);
    }

    public function testRows(): void
    {
        $ui = $this->mock(UI::class);

        $select = $this->mock(TableSelection::class);

        $repository = $this->mockMethod(DocumentRepository::class, 'all', [], [
            $this->mock(Document::class),
            $this->mock(Document::class)
        ]);

        $instance = new DocumentTable(
            $this->fail(...),
            $repository,
            $ui,
            $this->mock(DocumentModal::class),
            $this->mock(...),
            fn(DateTimeImmutable $d) => 'formatted date'
        );

        $rows = $instance->rows($select);
        $this->assertSame(2, count($rows));
        foreach ($rows as $row) {
            $this->assertSame([
                'order',
                'title',
                'created',
                'change',
                'criteria',
            ], array_keys($row));
        }

    }

    public function testSelect(): void
    {
        $rows = [
            $this->mock(Document::class),
            $this->mock(Document::class)
        ];

        $select = $this->mock(TableSelection::class);
        $repository = $this->mockMethod(DocumentRepository::class, 'all', [], $rows);

        $instance = new DocumentTable(
            $this->fail(...),
            $repository,
            $this->mock(UI::class),
            $this->mock(DocumentModal::class)
        );

        $this->assertSame($rows, $instance->select($select));
    }

    public function testRow(): void
    {
        $instance = new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $this->mock(...),
            fn(DateTimeImmutable $date) => 'formatted date'
        );

        $this->assertSame([
            'order',
            'title',
            'created',
            'change',
            'criteria',
        ], array_keys($instance->row($this->mock(Document::class), 1)));
    }

    public function testShowEmptyCriteria(): void
    {
        $ui = $this->mock(UI::class);
        $ui->method('txt')->willReturnCallback(fn($s) => 'txt: ' . $s);

        $instance = new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $ui,
            $this->mock(DocumentModal::class)
        );

        $this->assertSame('txt: tbl_docs_cell_not_criterion', $instance->showCriteria(
            $this->mockTree(Document::class, ['criteria' => []]),
            $this->fail(...)
        ));
    }

    public function testShowCriteria(): void
    {
        $ui = $this->mock(UI::class);
        $ui->method('txt')->willReturnCallback(fn($s) => 'txt: ' . $s);

        $instance = new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $ui,
            $this->mock(DocumentModal::class)
        );

        $criteria = [
            $this->mock(Criterion::class),
            $this->mock(Criterion::class),
        ];

        $this->assertSame(['a', 'b', 'a', 'b'], $instance->showCriteria(
            $this->mockTree(Document::class, ['criteria' => $criteria]),
            function (Criterion $criterion) use ($criteria) {
                $this->assertTrue(in_array($criterion, $criteria, true));
                return ['a', 'b'];
            }
        ));
    }

    public function testShowCriterion(): void
    {
        $content = $this->mock(CriterionContent::class);
        $legacy = $this->mock(Legacy::class);
        $component = $this->mock(Component::class);

        $ui = $this->mockTree(UI::class, ['create' => $this->mockMethod(UIFactory::class, 'legacy', ['<br/>'], $legacy)]);

        $instance = new DocumentTable(
            function (CriterionContent $c) use ($content, $component) {
                $this->assertSame($content, $c);
                return $component;
            },
            $this->mock(DocumentRepository::class),
            $ui,
            $this->mock(DocumentModal::class)
        );

        $criterion = $this->mockTree(Criterion::class, ['content' => $content]);

        $this->assertSame([$component, $legacy], $instance->showCriterion($criterion));
    }

    public function testCriterionName(): void
    {
        $content = $this->mock(CriterionContent::class);
        $component = $this->mock(Component::class);

        $instance = new DocumentTable(
            function (CriterionContent $c) use ($content, $component) {
                $this->assertSame($content, $c);
                return $component;
            },
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class)
        );

        $this->assertSame(
            $component,
            $instance->criterionName($this->mockTree(Criterion::class, ['content' => $content]))
        );
    }

    public function testOrderInputGui(): void
    {
        $input = null;
        $called = false;

        $create = function (string $class) use (&$called, &$input) {
            if ($called) {
                $this->fail('Called more than once');
            }
            $called = true;
            $input = $this->mock($class);
            return $input;
        };

        $instance = new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $create
        );

        $result = $instance->orderInputGui($this->mock(Document::class), 1);
        $this->assertSame($input, $result);
    }

    public function testUi(): void
    {
        $ui = $this->mock(UI::class);
        $instance = new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $ui,
            $this->mock(DocumentModal::class)
        );

        $this->assertSame($ui, $instance->ui());
    }

    public function testName(): void
    {
        $this->assertSame(DocumentTable::class, (new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class)
        ))->name());
    }
}
