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

use ILIAS\LegalDocuments\Value\History;
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\DI\LoggingServices;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\TableFilter;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Repository\HistoryRepository;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Table\HistoryTable;
use ilLogger;
use ilDateTime;
use DateTimeImmutable;

require_once __DIR__ . '/../ContainerMock.php';

class HistoryTableTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(HistoryTable::class, new HistoryTable(
            $this->mock(HistoryRepository::class),
            $this->mock(ProvideDocument::class),
            'reset',
            'auto-complete-link',
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $this->fail(...)
        ));
    }

    public function testColumns(): void
    {
        $ui = $this->mock(UI::class);
        $ui->expects(self::exactly(6))->method('txt')->willReturnCallback(fn($key) => 'txt: ' . $key);

        $instance = new HistoryTable(
            $this->mock(HistoryRepository::class),
            $this->mock(ProvideDocument::class),
            'reset',
            'auto-complete-link',
            $ui,
            $this->mock(DocumentModal::class),
            $this->fail(...)
        );

        $this->assertSame([
            'created' => ['txt: tbl_hist_head_acceptance_date', 'created'],
            'login' => ['txt: tbl_hist_head_login', 'login'],
            'firstname' => ['txt: tbl_hist_head_firstname', 'firstname'],
            'lastname' => ['txt: tbl_hist_head_lastname', 'lastname'],
            'document' => ['txt: tbl_hist_head_document', 'document'],
            'criteria' => ['txt: tbl_hist_head_criteria'],
        ], $instance->columns());
    }

    public function testConfig(): void
    {
        class_exists(ilDateTime::class, true); // Trigger autoload to ensure IL_CAL_UNIX is defined.
        $filter = $this->mock(TableFilter::class);
        $filter->expects(self::exactly(2))->method('addFilterItem');

        $config = $this->mock(TableConfig::class);
        $config->expects(self::once())->method('setTitle')->with('txt: acceptance_history');
        $config->expects(self::once())->method('setDefaultOrderField')->with('created');
        $config->expects(self::once())->method('setDefaultOrderDirection')->with('DESC');
        $config->expects(self::once())->method('setSelectableColumns')->with('firstname', 'lastname', 'criteria');
        $config->expects(self::once())->method('asFilter')->with('reset command')->willReturn($filter);

        $ui = $this->mock(UI::class);
        $ui->method('txt')->willReturnCallback(fn($key) => 'txt: ' . $key);

        $instance = new HistoryTable(
            $this->mock(HistoryRepository::class),
            $this->mock(ProvideDocument::class),
            'reset command',
            'auto-complete-link',
            $ui,
            $this->mock(DocumentModal::class),
            $this->mock(...)
        );

        $instance->config($config);
    }

    public function testRows(): void
    {
        $expected_filter = ['a' => 'b', 'start' => 'x', 'end' => 'y', 'c' => 'd'];
        $repository = $this->mock(HistoryRepository::class);
        $repository->expects(self::once())->method('countAll')->with($expected_filter)->willReturn(2);
        $repository->expects(self::once())->method('all')->with(
            $expected_filter,
            ['foo' => 'bar'],
            4,
            34
        )->willReturn([
            $this->mock(History::class),
            $this->mock(History::class),
        ]);

        $selection = $this->mockTree(TableSelection::class, [
            'filter' => ['a' => 'b','period' => ['start' => 'x', 'end' => 'y'] ,'c' => 'd'],
            'getOrderField' => 'foo',
            'getOrderDirection' => 'bar',
            'getOffset' => 4,
            'getLimit' => 34,
        ]);
        $selection->expects(self::once())->method('setMaxCount')->with(2);

        $instance = new HistoryTable(
            $repository,
            $this->mock(ProvideDocument::class),
            'reset command',
            'auto-complete-link',
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $this->mock(...),
            fn(DateTimeImmutable $date) => 'formated'
        );

        $this->assertSame([[
            'created' => 'formated',
            'login' => '',
            'firstname' => '',
            'lastname' => '',
            'document' => [],
            'criteria' => '',
        ], [
            'created' => 'formated',
            'login' => '',
            'firstname' => '',
            'lastname' => '',
            'document' => [],
            'criteria' => '',
        ]], $instance->rows($selection));
    }

    public function testName(): void
    {
        $instance = new HistoryTable(
            $this->mock(HistoryRepository::class),
            $this->mock(ProvideDocument::class),
            'reset command',
            'auto-complete-link',
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $this->fail(...)
        );

        $this->assertSame(HistoryTable::class, $instance->name());
    }
}
