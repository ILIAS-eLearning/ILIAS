<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\Data;
use \ILIAS\UI\Implementation\Component\Signal;

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

    public function p_getMultiActionsDropdown(array $actions, I\Signal $signal)
    {
        return $this->getMultiActionsDropdown($actions, $signal);
    }

    public function p_getSingleActionsForRow(string $row_id, array $actions)
    {
        return $this->getSingleActionsForRow($row_id, $actions);
    }

    public function p_renderTableHeader($tpl, array $columns)
    {
        return $this->renderTableHeader($tpl, $columns);
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
            $this->getRefinery()
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

    private function getDataFactory()
    {
        return new Data\Factory();
    }


    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function button()
            {
                return new I\Button\Factory();
            }
            public function dropdown()
            {
                return new I\Dropdown\Factory();
            }
        };
        return $factory;
    }

    public function testGetMultiActionHandler()
    {
        $renderer = $this->getRenderer();
        $signal = new I\Signal('signal_id');
        $closure = $renderer->p_getMultiActionHandler($signal);
        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $expected = $this->brutallyTrimHTML(
            "$(document).on('signal_id', function(event, signal_data) {"
        . "  il.UI.table.data.doAction('component_id', signal_data, il.UI.table.data.collectSelectedRowIds('component_id'));"
        . "  return false; "
        . "});"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetActionRegistrationSignal()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $signal = new I\Signal('signal_id');
        $action = $f->standard('label', 'param', $signal);
        $closure = $renderer->p_getActionRegistration('action_id', $action);

        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $expected = $this->brutallyTrimHTML(
            "il.UI.table.data.registerAction('component_id', 'action_id', 'SIGNAL', '{\"id\":\"signal_id\",\"options\":[]}', 'param');"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testGetActionRegistrationURL()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $action = $f->standard('label', 'param', $url);
        $closure = $renderer->p_getActionRegistration('action_id', $action);

        $actual = $this->brutallyTrimHTML($closure('component_id'));
        $expected = $this->brutallyTrimHTML(
            "il.UI.table.data.registerAction('component_id', 'action_id', 'URL', '$url', 'param');"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testMultiActionsDropdown()
    {
        $renderer = $this->getRenderer();
        $f = $this->getActionFactory();
        $signal = new I\Signal('signal_id');
        $url = $this->getDataFactory()->uri('http://wwww.ilias.de?ref_id=1');
        $actions = [
            $f->standard('label1', 'param', $signal),
            $f->standard('label2', 'param', $url)
        ];
        $this->assertNull(
            $renderer->p_getMultiActionsDropdown([], $signal)
        );
        $this->assertEquals(
            2,
            count($renderer->p_getMultiActionsDropdown($actions, $signal)->getItems())
        );
    }
    public function testSingleActionsDropdown()
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

    public function testRenderTableHeader()
    {
        $renderer = $this->getRenderer();
        $tpl = $this->getTemplateFactory()->getTemplate("src/UI/templates/default/Table/tpl.datatable.html", true, true);
        $f = $this->getColumnFactory();
        $columns = [
            'f1' => $f->text("Field 1")->withIndex(1),
            'f2' => $f->text("Field 2")->withIndex(2),
            'f3' => $f->number("Field 3")->withIndex(3)
        ];

        $renderer->p_renderTableHeader($tpl, $columns);

        $actual = $this->brutallyTrimHTML($tpl->get());
        $expected = <<<EOT
<div class="il-table-data" id="{ID}">
    <table class="table" role="grid" aria-labelledby="{ID}_label" aria-colcount="{COL_COUNT}">
        <thead>
            <tr class="header row" role="rowgroup">
                <th class="header cell rowselection" role="columnheader"></th>
                <th class="header cell" role="columnheader" aria-colindex="1" aria-sort="none">Field 1</th>
                <th class="header cell" role="columnheader" aria-colindex="2" aria-sort="none">Field 2</th>
                <th class="header cell" role="columnheader" aria-colindex="3" aria-sort="none">Field 3</th>
                <th class="header cell rowaction" role="columnheader"></th>
            </tr>
        </thead>
        <tbody class="body" role="rowgroup"></tbody>
    </table>
    <div class="multiaction-triggerer">{MULTI_ACTION_TRIGGERER}</div>
</div>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testRowFactory()
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
        $rf = new I\Table\RowFactory($columns, $actions);

        $this->assertInstanceOf(Component\Table\RowFactory::class, $rf);
        $row = $rf->standard('row_id-1', []);
        $this->assertInstanceOf(Component\Table\Row::class, $row);
        return [$rf, $columns, $actions];
    }

    /**
     * @depends testRowFactory
     */
    public function testStandardRowFromFactory(array $params) : I\Table\StandardRow
    {
        list($rf, $columns, $actions) = $params;
        $record = [
            'f1' => 'v1',
            'f2' => 'v2',
            'f3' => 'v3'
        ];
        $row = $rf->standard('row_id-1', $record);

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
     * @depends testStandardRowFromFactory
     */
    public function testRenderStandardRow(I\Table\StandardRow $row)
    {
        list($rf, $columns, $actions) = $params;
        $record = [
            'f1' => 'v1',
            'f2' => 'v2',
            'f3' => 'v3'
        ];
        $actual = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($row));
        $expected = <<<EOT
            <td class="cell rowselection" role="gridcell" tabindex="-1">
                <input type="checkbox" value="row_id-1" class="row-selector">
            </td>
            <td class="cell Text" role="gridcell" aria-colindex="1" tabindex="-1">v1</td>
            <td class="cell Text" role="gridcell" aria-colindex="2" tabindex="-1">v2</td>
            <td class="cell Number" role="gridcell" aria-colindex="3" tabindex="-1">v3</td>
            <td class="cell rowaction" role="gridcell" tabindex="-1">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="actions" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><button class="btn btn-link" id="id_1">label1</button></li>
                        <li><button class="btn btn-link" data-action="http://wwww.ilias.de?ref_id=1&param=row_id-1" id="id_2">label2</button></li>
                    </ul>
                </div>
            </td>
EOT;
        $expected = $this->brutallyTrimHTML($expected);
        $this->assertEquals($expected, $actual);
    }
}
