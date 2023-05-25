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
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Signal;
use Psr\Http\Message\ServerRequestInterface;

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
        \ILIAS\Data\Order $order
    ) {
        return $this->renderTableHeader($default_renderer, $component, $tpl, $order);
    }
}


/**
 * Tests for the Renderer of DataTables.
 */
class DataRendererTest extends ILIAS_UI_TestBase
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
            new \ILIAS\Data\Factory(),
            new \ILIAS\UI\Help\TextRetriever\Echoing()
        );
    }

    private function getActionFactory()
    {
        return new I\Table\Action\Factory();
    }

    private function getColumnFactory()
    {
        return new I\Table\Column\Factory();
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public function button(): \ILIAS\UI\Component\Button\Factory
            {
                return new I\Button\Factory();
            }
            public function dropdown(): ILIAS\UI\Component\Dropdown\Factory
            {
                return new I\Dropdown\Factory();
            }
            public function symbol(): ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function table(): ILIAS\UI\Component\Table\Factory
            {
                return new I\Table\Factory(
                    new I\SignalGenerator(),
                    new \ILIAS\Data\Factory(),
                    new I\Table\Column\Factory(),
                    new I\Table\Action\Factory(),
                    new I\Table\DataRowBuilder()
                );
            }
            public function divider(): ILIAS\UI\Component\Divider\Factory
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

    public function testDataTableGetActionRegistrationSignal()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $signal = new I\Signal('signal_id');
        $action = $f->standard('label', 'param', $signal);
        $closure = $renderer->p_getActionRegistration('action_id', $action);

        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $expected = $this->brutallyTrimHTML(
            "il.UI.table.data.get('component_id').registerAction('action_id', 'SIGNAL', '{\"id\":\"signal_id\",\"options\":[]}', 'param');"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testDataTableGetActionRegistrationURL()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $action = $f->standard('label', 'param', $url);
        $closure = $renderer->p_getActionRegistration('action_id', $action);

        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $expected = $this->brutallyTrimHTML(
            "il.UI.table.data.get('component_id').registerAction('action_id', 'URL', '$url', 'param');"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testDataTableMultiActionsDropdown()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $signal1 = new I\Signal('signal_id');
        $signal2 = new I\Signal('signal_id2');
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $actions = [
            $f->standard('label1', 'param', $signal1),
            $f->standard('label2', 'param', $url)
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
        $signal = new I\Signal('signal_id');
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $actions = [
            'a1' => $f->standard('label1', 'param', $signal),
            'a2' => $f->standard('label2', 'param', $url)
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
            'f2' => $f->text("Field 2")->withIndex(2),
            'f3' => $f->number("Field 3")->withIndex(3)
        ];
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method("getUri")
            ->willReturn(new class () {
                public function __toString()
                {
                    return 'http://localhost:80';
                }
            });

        $order = $data_factory->order('f1', \ILIAS\Data\Order::ASC);
        $table = $this->getUIFactory()->table()->data('', $columns, $data)
            ->withRequest($request);
        $renderer->p_renderTableHeader($this->getDefaultRenderer(), $table, $tpl, $order);

        $actual = $this->brutallyTrimHTML($tpl->get());
        $expected = <<<EOT
<div class="c-table-data" id="{ID}">
    <div class="viewcontrols">{VIEW_CONTROLS}</div>
    <table class="c-table-data__table" role="grid" aria-labelledby="{ID}_label" aria-colcount="{COL_COUNT}">
        <thead>
            <tr class="c-table-data__header c-table-data__row" role="rowgroup">
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" role="columnheader" tabindex="-1" aria-colindex="0" aria-sort="ascending">
                    <div class="c-table-data__header__resize-wrapper">
                        <a tabindex="0" class="glyph" href="http://localhost:80?tsort_f=f1&tsort_d=DESC" aria-label="sort_ascending"><span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span></a>
                        <button class="btn btn-link" data-action="http://localhost:80?tsort_f=f1&tsort_d=DESC" id="id_1">Field 1</button>
                    </div>
                </th>
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" role="columnheader" tabindex="-1" aria-colindex="1">
                    <div class="c-table-data__header__resize-wrapper">
                        <button class="btn btn-link" data-action="http://localhost:80?tsort_f=f2&tsort_d=ASC" id="id_2">Field 2</button>
                    </div>
                </th>
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--number" role="columnheader" tabindex="-1" aria-colindex="2">
                    <div class="c-table-data__header__resize-wrapper">
                        <button class="btn btn-link" data-action="http://localhost:80?tsort_f=f3&tsort_d=ASC" id="id_3">Field 3</button>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody class="c-table-data__body" role="rowgroup"></tbody>
    </table>
</div>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
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
        $signal = new I\Signal('signal_id');
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $actions = [
            'a1' => $f->standard('label1', 'param', $signal),
            'a2' => $f->standard('label2', 'param', $url)
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
<td class="c-table-data__cell c-table-data__cell--text " role="gridcell" aria-colindex="1" tabindex="-1">v1</td>
<td class="c-table-data__cell c-table-data__cell--text " role="gridcell" aria-colindex="2" tabindex="-1">v2</td>
<td class="c-table-data__cell c-table-data__cell--number " role="gridcell" aria-colindex="3" tabindex="-1">3</td>
<td class="c-table-data__cell c-table-data__rowaction" role="gridcell" tabindex="-1">
    <div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_2" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_2_menu"><span class="caret"></span></button>
        <ul id="id_2_menu" class="dropdown-menu"><li><button class="btn btn-link" id="id_1">label1</button></li><li><button class="btn btn-link" data-action="">label2</button></li></ul>
    </div>
</td>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }
}
