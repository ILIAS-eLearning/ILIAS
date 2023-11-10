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

use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\EditLinks;
use ILIAS\LegalDocuments\Table\DocumentTable;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Table\EditableDocumentTable;

require_once __DIR__ . '/../ContainerMock.php';

class EditableDocumentTableTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(EditableDocumentTable::class, new EditableDocumentTable($this->mock(DocumentTable::class), $this->mock(EditLinks::class)));
    }

    public function testColumns(): void
    {
        $instance = new EditableDocumentTable($this->mockTree(DocumentTable::class, [
            'columns' => ['foo' => 'bar', 'a' => 'b'],
            'ui' => $this->mockMethod(UI::class, 'txt', ['actions'], 'txt: actions'),
        ]), $this->mock(EditLinks::class));

        $this->assertSame([
            'delete' => [' ', '', '1%', true],
            'foo' => 'bar',
            'a' => 'b',
            'actions' => ['txt: actions', '', '10%']
        ], $instance->columns());
    }

    public function testConfig(): void
    {
        $config = $this->mock(TableConfig::class);
        $config->expects(self::once())->method('addMultiCommand')->with('deleteDocuments', 'txt: delete');
        $config->expects(self::once())->method('addCommandButton')->with('saveOrder', 'txt: sorting_save');

        $ui = $this->mock(UI::class);
        $ui->method('txt')->willReturnCallback(fn($key) => 'txt: ' . $key);

        $table = $this->mockTree(DocumentTable::class, ['ui' => $ui]);
        $table->expects(self::once())->method('config')->with($config);

        $instance = new EditableDocumentTable($table, $this->mock(EditLinks::class));

        $instance->config($config);
    }

    public function testRows(): void
    {
        $select = $this->mock(TableSelection::class);
        $document = $this->mock(Document::class);
        $table = $this->mock(DocumentTable::class);

        $table->expects(self::once())->method('select')->with($select)->willReturn([$document]);
        $table->expects(self::once())->method('row')->with($document)->willReturn([
            'foo' => 'bar',
            'a' => 'b'
        ]);

        $instance = new EditableDocumentTable($table, $this->mock(EditLinks::class));

        $rows = $instance->rows($select);
        $this->assertSame(1, count($rows));
        $this->assertSame(['delete', 'order', 'a', 'criteria', 'actions'], array_keys(current($rows)));
    }

    public function testRow(): void
    {
        $instance = new EditableDocumentTable($this->mock(DocumentTable::class), $this->mock(EditLinks::class));

        $row = $instance->row($this->mock(Document::class));
        $this->assertSame(['delete', 'order', 'criteria', 'actions'], array_keys($row));
    }

    public function testName(): void
    {
        $instance = new EditableDocumentTable($this->mock(DocumentTable::class), $this->mock(EditLinks::class));
        $this->assertSame(EditableDocumentTable::class, $instance->name());
    }
}
