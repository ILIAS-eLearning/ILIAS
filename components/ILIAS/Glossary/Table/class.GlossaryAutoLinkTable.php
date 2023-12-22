<?php

declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\components\ILIAS\Glossary\Table;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Glossary\Editing\EditingGUIRequest;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class GlossaryAutoLinkTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilObjGlossary $glossary;
    protected EditingGUIRequest $edit_gui_request;
    protected string $requested_table_glossary_auto_link_action = "";

    /**
     * @var string[]
     */
    protected array $requested_table_glossary_auto_link_ids = [];

    public function __construct(\ilObjGlossary $glossary)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->glossary = $glossary;
        $this->edit_gui_request = $DIC->glossary()->internal()->gui()->editing()->request();
        $this->requested_table_glossary_auto_link_action = $this->edit_gui_request->getTableGlossaryAutoLinkAction();
        $this->requested_table_glossary_auto_link_ids = $this->edit_gui_request->getTableGlossaryAutoLinkIds();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        if ($this->requested_table_glossary_auto_link_action === "removeGlossary") {
            $items = [];
            foreach ($this->requested_table_glossary_auto_link_ids as $id) {
                $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                    $id,
                    \ilObject::_lookupTitle((int) $id)
                );
            }
            echo($this->ui_ren->renderAsync([
                $this->ui_fac->modal()->interruptive(
                    "",
                    $this->lng->txt("glo_remove_glossary"),
                    $this->ctrl->getFormActionByClass("ilobjglossarygui", "removeGlossary")
                )
                             ->withAffectedItems($items)
                             ->withActionButtonLabel($this->lng->txt("remove"))
            ]));
            exit();
        }

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("cont_auto_glossaries"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title"))
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["glo_auto_link_table"];

        $url_builder_remove = new UI\URLBuilder($this->df->uri($this->request->getUri()->__toString()));
        list($url_builder_remove, $action_parameter_token_remove, $row_id_token_remove) =
            $url_builder_remove->acquireParameters(
                $query_params_namespace,
                "action",
                "glo_ids"
            );

        $actions["remove"] = $this->ui_fac->table()->action()->single(
            $this->lng->txt("remove"),
            $url_builder_remove->withParameter($action_parameter_token_remove, "removeGlossary"),
            $row_id_token_remove
        )
                                          ->withAsync();

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->glossary
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected \ilObjGlossary $glossary
            ) {
            }

            public function getRows(
                UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($range, $order);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record["glo_id"];

                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null, Data\Order $order = null): array
            {
                $records = [];
                $i = 0;
                foreach ($this->glossary->getAutoGlossaries() as $glo_id) {
                    $records[$i]["glo_id"] = $glo_id;
                    $records[$i]["title"] = \ilObject::_lookupTitle($glo_id);
                    $i++;
                }

                if ($order) {
                    $records = $this->orderRecords($records, $order);
                }

                if ($range) {
                    $records = $this->limitRecords($records, $range);
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
