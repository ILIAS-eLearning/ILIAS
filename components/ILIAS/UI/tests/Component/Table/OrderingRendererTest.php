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

require_once("vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/TableRendererTestBase.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Table\Renderer;
use ILIAS\Data;

/**
 * Tests for the Renderer of DataTables.
 */
class OrderingRendererTest extends TableRendererTestBase
{
    private function getRenderer()
    {
        return new Renderer(
            $this->getUIFactory(),
            $this->getTemplateFactory(),
            $this->getLanguage(),
            $this->getJavaScriptBinding(),
            new ilImagePathResolver(),
            new \ILIAS\Data\Factory(),
            new \ILIAS\UI\Help\TextRetriever\Echoing(),
            $this->getUploadLimitResolver()
        );
    }

    public function testOrderingTableRenderTableHeaderWithoutActions()
    {
        $renderer = $this->getRenderer();
        $f = $this->getColumnFactory();
        $data = new class () implements ILIAS\UI\Component\Table\OrderingRetrieval {
            public function getRows(
                Component\Table\OrderingRowBuilder $row_builder,
                array $visible_column_ids
            ): \Generator {
                if (false) {
                    yield;
                }
            }
        };
        $columns = [
            'f1' => $f->text("Field 1")->withIndex(1),
            'f2' => $f->text("Field 2")->withIndex(2),
            'f3' => $f->number("Field 3")->withIndex(3)
        ];
        $uri = new Data\URI('https://localhost');
        $table = $this->getUIFactory()->table()->ordering($data, $uri, '', $columns)
            ->withRequest($this->getDummyRequest());

        $actual = $renderer->renderOrderingTable($table, $this->getDefaultRenderer());
        $expected = <<<EOT
<div class="c-table-ordering" id="id_1"><h2 class="ilHeader" id="id_1_label"></h2>
    <div class="viewcontrols">
        <form class="il-viewcontrols-form l-bar__space-keeper" method="get" id="id_2"></form>
    </div>
    <form method="post" class="c-table-data__table-wrapper c-table-ordering__form" action="https://localhost">
        <table class="c-table-data__table" aria-labelledby="id_1_label" aria-colcount="5" role="grid">
            <thead>
            <tr class="c-table-data__header c-table-data__row">
                <th class="c-table-data__header c-table-data__cell c-table-data__header__rowselection" tabindex="-1" aria-colindex="1"></th>
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--number" tabindex="-1" aria-colindex="2">
                    <div class="c-table-data__header__resize-wrapper">table_posinput_col_title</div>
                </th>
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" tabindex="-1" aria-colindex="3">
                    <div class="c-table-data__header__resize-wrapper">Field 1</div>
                </th>
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--text" tabindex="-1" aria-colindex="4">
                    <div class="c-table-data__header__resize-wrapper">Field 2</div>
                </th>
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--number" tabindex="-1" aria-colindex="5">
                    <div class="c-table-data__header__resize-wrapper">Field 3</div>
                </th>
            </tr>
            </thead>
            <tbody class="c-table-data__body">
            <tr>
                <td class="c-table-data__cell c-table-data__cell--multiaction" colspan="5">
                    <div class="l-bar__space-keeper">
                        <div class="l-bar__element">
                            <div class="c-table-data__multiaction-triggerer"></div>
                        </div>
                        <div class="l-bar__element">
                            <button class="btn btn-default" data-action="" id="id_1">sorting_save</button>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
    <div class="c-table-data__async_modal_container"></div>
    <div class="c-table-data__async_message modal" role="dialog" id="id_1_msgmodal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close">
                        <span aria-hidden="true">&times;</span></button>
                </div>
                <div class="c-table-data__async_messageresponse modal-body"></div>
            </div>
        </div>
    </div>
</div>
EOT;
        $this->assertEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }
}
