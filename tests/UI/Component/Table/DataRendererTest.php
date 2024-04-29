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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/TableTestBase.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Signal;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\URLBuilder;

/**
 * wrapper around the renderer to expose protected functions
 */
class DTRenderer extends I\Table\Renderer
{
    public function p_getMultiActionHandler(I\Signal $signal)
    {
        return $this->getMultiActionHandler($signal);
    }

    public function p_getActionRegistration(string $action_id, I\Table\Action\Action $action)
    {
        return $this->getActionRegistration($action_id, $action);
    }

    public function p_buildMultiActionsDropdown(
        array $actions,
        I\Signal $action_signal,
        I\Signal $modal_signal
    ) {
        return $this->buildMultiActionsDropdown($actions, $action_signal, $modal_signal);
    }

    public function p_getSingleActionsForRow(string $row_id, array $actions)
    {
        return $this->getSingleActionsForRow($row_id, $actions);
    }

    public function p_renderTableHeader(
        TestDefaultRenderer $default_renderer,
        I\Table\Data $component,
        $tpl,
        ?I\Signal $sortation_signal
    ) {
        return $this->renderTableHeader($default_renderer, $component, $tpl, $sortation_signal);
    }
}


/**
 * Tests for the Renderer of DataTables.
 */
class DataRendererTest extends TableTestBase
{
    private function getRenderer()
    {
        return new DTRenderer(
            $this->getUIFactory(),
            $this->getTemplateFactory(),
            $this->getLanguage(),
            $this->getJavaScriptBinding(),
            $this->getRefinery(),
            new ilImagePathResolver(),
            new \ILIAS\Data\Factory()
        );
    }

    private function getActionFactory()
    {
        return new I\Table\Action\Factory();
    }

    private function getColumnFactory()
    {
        return new I\Table\Column\Factory(
            $this->getLanguage()
        );
    }

    private function getDummyRequest()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method("getUri")
            ->willReturn(new class () {
                public function __toString()
                {
                    return 'http://localhost:80';
                }
            });

        $request
            ->method("getQueryParams")
            ->willReturn([]);
        return $request;
    }

    public function getDataFactory(): Data\Factory
    {
        return new Data\Factory();
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class ($this->getTableFactory()) extends NoUIFactory {
            protected Component\Table\Factory $table_factory;
            public function __construct(
                Component\Table\Factory $table_factory
            ) {
                $this->table_factory = $table_factory;
            }
            public function button(): Component\Button\Factory
            {
                return new I\Button\Factory();
            }
            public function dropdown(): Component\Dropdown\Factory
            {
                return new I\Dropdown\Factory();
            }
            public function symbol(): Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function table(): Component\Table\Factory
            {
                return $this->table_factory;
            }
            public function divider(): Component\Divider\Factory
            {
                return new I\Divider\Factory();
            }
        };
        return $factory;
    }

    public function testDataTableGetMultiActionHandler()
    {
        $renderer = $this->getRenderer();
        $signal = new I\Signal('signal_id');
        $closure = $renderer->p_getMultiActionHandler($signal);
        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $expected = $this->brutallyTrimHTML(
            "$(document).on('signal_id', function(event, signal_data) { 
                il.UI.table.data.get('component_id').doMultiAction(signal_data); 
                return false; 
            });"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testDataTableGetActionRegistration()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $url_builder = new URLBuilder($url);
        list($builder, $token) = $url_builder->acquireParameter(['namespace'], 'param');

        $action = $f->standard('label', $builder, $token);
        $closure = $renderer->p_getActionRegistration('action_id', $action);

        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $url = $url->__toString();
        $expected = $this->brutallyTrimHTML(
            'il.UI.table.data.get(\'component_id\').registerAction(\'action_id\', false, new il.UI.core.URLBuilder(new URL("http://wwww.ilias.de?ref_id=1&namespace_param="), new Map([["namespace_param",new il.UI.core.URLBuilderToken(["namespace"], "param",'
        );
        $this->assertStringStartsWith($expected, $actual);
    }

    public function testDataTableMultiActionsDropdown()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $signal1 = new I\Signal('signal_id');
        $signal2 = new I\Signal('signal_id2');
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $url_builder = new URLBuilder($url);
        list($builder, $token) = $url_builder->acquireParameter(['namespace'], 'param');
        $actions = [
            $f->standard('label1', $builder, $token),
            $f->standard('label2', $builder, $token)
        ];
        $this->assertNull(
            $renderer->p_buildMultiActionsDropdown([], $signal1, $signal2)
        );
        $this->assertEquals(
            4, //2 actions, 1 divider, one all-action
            count($renderer->p_buildMultiActionsDropdown($actions, $signal1, $signal2)->getItems())
        );
    }
    public function testDataTableSingleActionsDropdown()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $url_builder = new URLBuilder($url);
        list($builder, $token) = $url_builder->acquireParameter(['namespace'], 'param');
        $actions = [
            'a1' => $f->standard('label1', $builder, $token)->withAsync(),
            'a2' => $f->standard('label2', $builder, $token)
        ];
        $this->assertEquals(
            2,
            count($renderer->p_getSingleActionsForRow('row_id-1', $actions)->getItems())
        );
    }

    public function testDataTableRenderTableHeader()
    {
        $renderer = $this->getRenderer();
        $data_factory = new \ILIAS\Data\Factory();
        $tpl = $this->getTemplateFactory()->getTemplate("src/UI/templates/default/Table/tpl.datatable.html", true, true);
        $f = $this->getColumnFactory();
        $data = new class () implements ILIAS\UI\Component\Table\DataRetrieval {
            public function getRows(
                Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                yield $row_builder->buldDataRow('', []);
            }
            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return null;
            }
        };
        $columns = [
            'f1' => $f->text("Field 1")->withIndex(1),
            'f2' => $f->text("Field 2")->withIndex(2)->withIsSortable(false),
            'f3' => $f->number("Field 3")->withIndex(3)
        ];
        $sortation_signal = new I\Signal('sort_header_signal_id');
        $sortation_signal->addOption('value', 'f1:ASC');
        $table = $this->getUIFactory()->table()->data('', $columns, $data)
            ->withRequest($this->getDummyRequest());
        $renderer->p_renderTableHeader($this->getDefaultRenderer(), $table, $tpl, $sortation_signal);

        $actual = $this->brutallyTrimHTML($tpl->get());
        $expected = <<<EOT
<div class="c-table-data" id="{ID}">
    <div class="viewcontrols">{VIEW_CONTROLS}</div>
    <div class="c-table-data__table-wrapper">
        <table class="c-table-data__table" role="grid" aria-labelledby="{ID}_label" aria-colcount="{COL_COUNT}">
            <thead>
                <tr class="c-table-data__header c-table-data__row" role="rowgroup">
                    <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" role="columnheader" tabindex="-1" aria-colindex="0" aria-sort="order_option_generic_ascending">
                        <div class="c-table-data__header__resize-wrapper">
                            <a tabindex="0" class="glyph" href="#" aria-label="sort_ascending" id="id_2"><span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span></a>
                            <button class="btn btn-link" id="id_1">Field 1</button>
                        </div>
                    </th>
                    <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" role="columnheader" tabindex="-1" aria-colindex="1">
                        <div class="c-table-data__header__resize-wrapper">Field 2</div>
                    </th>
                    <th class="c-table-data__header c-table-data__cell c-table-data__cell--number" role="columnheader" tabindex="-1" aria-colindex="2">
                        <div class="c-table-data__header__resize-wrapper">
                            <button class="btn btn-link" id="id_3">Field 3</button>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="c-table-data__body" role="rowgroup"></tbody>
        </table>
    </div>
    <div class="c-table-data__async_modal_container"></div>

    <div class="c-table-data__async_message modal" role="dialog" id="{ID}_msgmodal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="c-table-data__async_messageresponse modal-body"></div>
            </div>
        </div>
    </div>

</div>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }


    public function testDataTableRenderHeaderWithoutSortableColums(): void
    {
        $renderer = $this->getRenderer();
        $data_factory = new \ILIAS\Data\Factory();
        $tpl = $this->getTemplateFactory()->getTemplate("src/UI/templates/default/Table/tpl.datatable.html", true, true);
        $f = $this->getColumnFactory();
        $data = new class () implements ILIAS\UI\Component\Table\DataRetrieval {
            public function getRows(
                Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                yield $row_builder->buldDataRow('', []);
            }
            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return null;
            }
        };
        $columns = [
            'f1' => $f->text("Field 1")->withIsSortable(false),
            'f2' => $f->text("Field 2")->withIsSortable(false)
        ];

        $sortation_signal = null;

        $table = $this->getUIFactory()->table()->data('', $columns, $data)
            ->withRequest($this->getDummyRequest());
        $renderer->p_renderTableHeader($this->getDefaultRenderer(), $table, $tpl, $sortation_signal);
        $actual = $this->brutallyTrimHTML($tpl->get());
        $expected = <<<EOT
<div class="c-table-data" id="{ID}">
    <div class="viewcontrols">{VIEW_CONTROLS}</div>
    <div class="c-table-data__table-wrapper">
        <table class="c-table-data__table" role="grid" aria-labelledby="{ID}_label" aria-colcount="{COL_COUNT}">
            <thead>
                <tr class="c-table-data__header c-table-data__row" role="rowgroup">
                    <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" role="columnheader" tabindex="-1" aria-colindex="0">
                        <div class="c-table-data__header__resize-wrapper">Field 1</div>
                    </th>
                    <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" role="columnheader" tabindex="-1" aria-colindex="1">
                        <div class="c-table-data__header__resize-wrapper">Field 2</div>
                    </th>
                </tr>
            </thead>
            <tbody class="c-table-data__body" role="rowgroup"></tbody>
        </table>
    </div>

    <div class="c-table-data__async_modal_container"></div>

    <div class="c-table-data__async_message modal" role="dialog" id="{ID}_msgmodal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="c-table-data__async_messageresponse modal-body"></div>
            </div>
        </div>
    </div>

</div>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testDataTableRenderHeaderWithActions(): void
    {
        $renderer = $this->getRenderer();
        $data_factory = new \ILIAS\Data\Factory();
        $tpl = $this->getTemplateFactory()->getTemplate("src/UI/templates/default/Table/tpl.datatable.html", true, true);
        $f = $this->getColumnFactory();

        $url = $data_factory->uri('http://wwww.ilias.de?ref_id=1');
        $url_builder = new URLBuilder($url);
        list($builder, $token) = $url_builder->acquireParameter(['namespace'], 'param');
        $actions = [
            'a2' => $this->getActionFactory()->standard('some action', $builder, $token)
        ];

        $data = new class () implements ILIAS\UI\Component\Table\DataRetrieval {
            public function getRows(
                Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                yield $row_builder->buldDataRow('', []);
            }
            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return null;
            }
        };
        $columns = [
            'f1' => $f->text("Field 1")->withIsSortable(false),
        ];

        $sortation_signal = null;

        $table = $this->getUIFactory()->table()->data('', $columns, $data)
            ->withActions($actions)
            ->withRequest($this->getDummyRequest());
        $renderer->p_renderTableHeader($this->getDefaultRenderer(), $table, $tpl, $sortation_signal);
        $actual = $this->brutallyTrimHTML($tpl->get());

        $expected = '<th class="c-table-data__header c-table-data__cell c-table-data__header__rowaction" role="columnheader" aria-colindex="1">actions</th>';
        $this->assertStringContainsString($expected, $actual);
    }

    public function testDataTableRowBuilder()
    {
        $f = $this->getColumnFactory();
        $columns = [
            'f1' => $f->text("Field 1")->withIndex(1),
            'f2' => $f->text("Field 2")->withIndex(2),
            'f3' => $f->number("Field 3")->withIndex(3)
        ];
        $f = $this->getActionFactory();
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $url_builder = new URLBuilder($url);
        list($builder, $token) = $url_builder->acquireParameter(['namespace'], 'param');
        $actions = [
            'a1' => $f->standard('label1', $builder, $token)->withAsync(),
            'a2' => $f->standard('label2', $builder, $token)
        ];

        $rb = (new I\Table\DataRowBuilder())
            ->withMultiActionsPresent(true)
            ->withSingleActions($actions)
            ->withVisibleColumns($columns);

        $row = $rb->buildDataRow('row_id-1', []);
        $this->assertInstanceOf(Component\Table\DataRow::class, $row);

        return [$rb, $columns, $actions];
    }

    /**
     * @depends testDataTableRowBuilder
     */
    public function testDataTableDataRowFromBuilder(array $params): I\Table\DataRow
    {
        list($rb, $columns, $actions) = $params;
        $record = [
            'f1' => 'v1',
            'f2' => 'v2',
            'f3' => 3
        ];
        $row = $rb->buildDataRow('row_id-1', $record);

        $this->assertEquals(
            $columns,
            $row->getColumns()
        );
        $this->assertEquals(
            $actions,
            $row->getActions()
        );
        $this->assertEquals(
            $record['f2'],
            $row->getCellContent('f2')
        );

        return $row;
    }

    /**
     * @depends testDataTableDataRowFromBuilder
     */
    public function testDataTableRenderStandardRow(I\Table\DataRow $row)
    {
        $actual = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($row));
        $expected = <<<EOT
<td class="c-table-data__cell c-table-data__rowselection" role="gridcell" tabindex="-1">
    <input type="checkbox" value="row_id-1" class="c-table-data__row-selector">
</td>
<td class="c-table-data__cell c-table-data__cell--text " role="gridcell" aria-colindex="1" tabindex="-1">
    <span class="c-table-data__cell__col-title">Field 1:</span>v1
</td>
<td class="c-table-data__cell c-table-data__cell--text " role="gridcell" aria-colindex="2" tabindex="-1">
    <span class="c-table-data__cell__col-title">Field 2:</span>v2
</td>
<td class="c-table-data__cell c-table-data__cell--number " role="gridcell" aria-colindex="3" tabindex="-1">
    <span class="c-table-data__cell__col-title">Field 3:</span>3
</td>
<td class="c-table-data__cell c-table-data__rowaction" role="gridcell" tabindex="-1">
    <div class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu"><span class="caret"></span></button>
        <ul id="id_3_menu" class="dropdown-menu">
            <li><button class="btn btn-link" data-action="http://wwww.ilias.de?ref_id=1&namespace_param%5B%5D=row_id-1" id="id_1">label1</button></li>
            <li><button class="btn btn-link" data-action="http://wwww.ilias.de?ref_id=1&namespace_param%5B%5D=row_id-1" id="id_2">label2</button></li>
        </ul>
    </div>
</td>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testRenderEmptyDataCell(): void
    {
        $data = new class () implements Component\Table\DataRetrieval {
            public function getRows(
                Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): Generator {
                yield from [];
            }

            public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
            {
                return 0;
            }
        };

        $columns = [
            'f1' => $this->getUIFactory()->table()->column()->text('f1'),
            'f2' => $this->getUIFactory()->table()->column()->text('f2'),
            'f3' => $this->getUIFactory()->table()->column()->text('f3'),
            'f4' => $this->getUIFactory()->table()->column()->text('f4'),
            'f5' => $this->getUIFactory()->table()->column()->text('f5'),
        ];

        $table = $this->getTableFactory()->data('', $columns, $data)
            ->withRequest($this->getDummyRequest());

        $html = $this->getDefaultRenderer()->render($table);

        $translation = $this->getLanguage()->txt('ui_table_no_records');
        $column_count = count($columns);

        // check that the empty cell is stretched over all columns.
        $this->assertTrue(str_contains($html, "colspan=\"$column_count\""));
        // check that the cell contains the default message.
        $this->assertTrue(str_contains($html, $translation));
    }
}
