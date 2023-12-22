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
class TermListTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected \ilGlobalTemplateInterface $tpl;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilObjGlossary $glossary;
    protected int $tax_node;
    protected \ilGlossaryTermPermission $term_perm;
    protected array $adv_cols_order = [];
    protected \ILIAS\AdvancedMetaData\Services\SubObjectModes\DataTable\SupplierInterface $adv_term_mode;
    protected EditingGUIRequest $edit_gui_request;
    protected string $requested_table_term_list_action = "";

    /**
     * @var string[]
     */
    protected array $requested_table_term_list_ids = [];

    public function __construct(\ilObjGlossary $glossary, int $tax_node)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->glossary = $glossary;
        $this->tax_node = $tax_node;
        $this->term_perm = \ilGlossaryTermPermission::getInstance();
        $this->edit_gui_request = $DIC->glossary()->internal()->gui()->editing()->request();
        $this->requested_table_term_list_action = $this->edit_gui_request->getTableGlossaryTermListAction();
        $this->requested_table_term_list_ids = $this->edit_gui_request->getTableGlossaryTermListIds();

        $adv_service = new \ILIAS\AdvancedMetaData\Services\Services();
        $this->adv_term_mode = $adv_service->forSubObjects(
            "glo",
            $this->glossary->getRefId(),
            "term"
        )->inDataTable();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        if ($this->requested_table_term_list_action === "deleteTerms") {
            $items = [];
            foreach ($this->requested_table_term_list_ids as $id) {
                if ($id === "ALL_OBJECTS") {
                    $filter_term = "";
                    $filter_def = "";
                    $data = $this->glossary->getTermList(
                        $filter_term,
                        "",
                        $filter_def,
                        $this->tax_node,
                        true,
                        true,
                        null,
                        false,
                        true
                    );

                    $terms = [];
                    foreach ($data as $term) {
                        $term_id = $term["id"];
                        $add = $this->handleTermForModal((int) $term_id);
                        $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                            (string) $term_id,
                            \ilGlossaryTerm::_lookGlossaryTerm((int) $term_id),
                            null,
                            $add
                        );
                    }
                } else {
                    $add = $this->handleTermForModal((int) $id);
                    $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                        $id,
                        \ilGlossaryTerm::_lookGlossaryTerm((int) $id),
                        null,
                        $add
                    );
                }
            }
            echo($this->ui_ren->renderAsync([
                $this->ui_fac->modal()->interruptive(
                    "",
                    empty($items) ? $this->lng->txt("no_checkbox") : $this->lng->txt("info_delete_sure"),
                    $this->ctrl->getFormActionByClass("ilobjglossarygui", "deleteTerms")
                )
                             ->withAffectedItems($items)
                             ->withActionButtonLabel(empty($items) ? $this->lng->txt("ok") : $this->lng->txt("delete"))
            ]));
            exit();
        }

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("cont_terms"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function handleTermForModal(int $id): string
    {
        $term_glo_id = \ilGlossaryTerm::_lookGlossaryID($id);
        if ($term_glo_id != $this->glossary->getId()
            && !\ilGlossaryTermReferences::isReferenced([$this->glossary->getId()], $id)
        ) {
            //TODO: How to handle redirects in modals?
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("glo_term_must_belong_to_glo"), true);
            $this->ctrl->redirectByClass("ilobjglossarygui", "listTerms");
        }

        $add = "";
        $nr = \ilGlossaryTerm::getNumberOfUsages($id);
        if ($nr > 0) {
            $this->ctrl->setParameterByClass(
                "ilglossarytermgui",
                "term_id",
                $id
            );

            if (\ilGlossaryTermReferences::isReferenced([$this->glossary->getId()], $id)) {
                $add = " (" . $this->lng->txt("glo_term_reference") . ")";
            } else {
                $link = $this->ui_fac->link()->standard(
                    $this->lng->txt("glo_list_usages"),
                    $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listUsages")
                );
                $add = sprintf($this->lng->txt("glo_term_is_used_n_times"), $nr)
                    . " [" . $this->ui_ren->render($link) . "]";
            }
        }

        return $add;
    }

    protected function getColumns(): array
    {
        $columns = [];
        $adv_columns = [];

        $adv_ap = new \ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_cols_order = $adv_ap->getColumnOrder();
        foreach ($this->adv_cols_order as $c) {
            $id = $c["id"];
            if ($id == 0) {
                $columns["term"] = $this->ui_fac->table()->column()->text($this->lng->txt("cont_term"));
            } else {
                if (empty($adv_columns)) {
                    $adv_columns = $this->adv_term_mode->getColumns();
                }
            }
        }

        foreach ($adv_columns as $k => $adv_column) {
            $adv_column = $adv_column->withIsOptional(true, false);
            $adv_columns[$k] = $adv_column;
        }
        $columns = array_merge($columns, $adv_columns);

        $columns["language"] = $this->ui_fac->table()->column()->text($this->lng->txt("language"))
                                            ->withIsSortable(false)
                                            ->withIsOptional(true, true);
        $columns["usage"] = $this->ui_fac->table()->column()->number($this->lng->txt("cont_usage"))
                                         ->withIsSortable(false)
                                         ->withIsOptional(true, true);
        $columns["usage_link"] = $this->ui_fac->table()->column()->link($this->lng->txt("usage_link"))
                                              ->withIsSortable(false)
                                              ->withIsOptional(true, true);
        $columns["definitions"] = $this->ui_fac->table()->column()->text($this->lng->txt("cont_definitions"))
                                               ->withIsSortable(false);

        if ($this->glossary->getVirtualMode() === "coll"
            || \ilGlossaryTermReferences::hasReferences($this->glossary->getId())
        ) {
            $columns["glossary"] = $this->ui_fac->table()->column()->text($this->lng->txt("obj_glo"))
                                                ->withIsSortable(false);
        }

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["glo_term_list_table"];

        $uri_copy = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilobjglossarygui", "copyTerms")
        );
        $url_builder_copy = new UI\URLBuilder($uri_copy);
        list($url_builder_copy, $action_parameter_token_copy, $row_id_token_copy) =
            $url_builder_copy->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $uri_reference = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilobjglossarygui", "referenceTerms")
        );
        $url_builder_reference = new UI\URLBuilder($uri_reference);
        list($url_builder_reference, $action_parameter_token_reference, $row_id_token_reference) =
            $url_builder_reference->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $url_builder_delete = new UI\URLBuilder($this->df->uri($this->request->getUri()->__toString()));
        list($url_builder_delete, $action_parameter_token_delete, $row_id_token_delete) =
            $url_builder_delete->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $uri_edit_term = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "editTerm")
        );
        $url_builder_edit_term = new UI\URLBuilder($uri_edit_term);
        list($url_builder_edit_term, $action_parameter_token_edit_term, $row_id_token_edit_term) =
            $url_builder_edit_term->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $uri_edit_definition = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(["ilglossarytermgui",
                                                                            "iltermdefinitioneditorgui",
                                                                            "ilglossarydefpagegui"], "edit")
        );
        $url_builder_edit_definition = new UI\URLBuilder($uri_edit_definition);
        list($url_builder_edit_definition, $action_parameter_token_edit_definition, $row_id_token_edit_definition) =
            $url_builder_edit_definition->acquireParameters(
                $query_params_namespace,
                "action",
                "term_ids"
            );

        $actions = [
            "copy" => $this->ui_fac->table()->action()->multi(
                $this->lng->txt("copy"),
                $url_builder_copy->withParameter($action_parameter_token_copy, "copyTerms"),
                $row_id_token_copy
            ),
            "reference" => $this->ui_fac->table()->action()->multi(
                $this->lng->txt("glo_reference"),
                $url_builder_reference->withParameter($action_parameter_token_reference, "referenceTerms"),
                $row_id_token_reference
            ),
            "delete" => $this->ui_fac->table()->action()->multi(
                $this->lng->txt("delete"),
                $url_builder_delete->withParameter($action_parameter_token_delete, "deleteTerms"),
                $row_id_token_delete
            )
                ->withAsync()
        ];

        $actions["edit_term"] = $this->ui_fac->table()->action()->single(
            $this->lng->txt("cont_edit_term"),
            $url_builder_edit_term->withParameter($action_parameter_token_edit_term, "editTerm"),
            $row_id_token_edit_term
        );

        $actions["edit_definition"] = $this->ui_fac->table()->action()->single(
            $this->lng->txt("cont_edit_definition"),
            $url_builder_edit_definition->withParameter($action_parameter_token_edit_definition, "editDefinition"),
            $row_id_token_edit_definition
        );

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->glossary,
            $this->tax_node,
            $this->adv_cols_order,
            $this->adv_term_mode,
            $this->ui_fac,
            $this->df,
            $this->lng,
            $this->term_perm
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            protected \ilCtrl $ctrl;

            public function __construct(
                protected \ilObjGlossary $glossary,
                protected int $tax_node,
                protected array $adv_cols_order,
                protected \ILIAS\AdvancedMetaData\Services\SubObjectModes\DataTable\SupplierInterface $adv_term_mode,
                protected UI\Factory $ui_fac,
                protected Data\Factory $df,
                protected \ilLanguage $lng,
                protected \ilGlossaryTermPermission $term_perm
            ) {
                global $DIC;

                $this->ctrl = $DIC->ctrl();
            }

            public function getRows(
                UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($range, $order, $filter_data);
                foreach ($records as $idx => $record) {
                    $row_id = (int) $record["term_id"];

                    $data_row = $row_builder->buildDataRow((string) $row_id, $record);
                    if (!($this->term_perm->checkPermission("write", $row_id)
                        || $this->term_perm->checkPermission("edit_content", $row_id))) {
                        if (!(\ilGlossaryTerm::_lookGlossaryID($row_id) == $this->glossary->getId()
                            || \ilGlossaryTermReferences::isReferenced([$this->glossary->getId()], $row_id))) {
                            $data_row = $data_row->withDisabledAction("edit_term");
                            $data_row = $data_row->withDisabledAction("edit_definition");
                        }
                    }
                    yield $data_row;
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null, Data\Order $order = null, ?array $filter_data = null): array
            {
                $filter_term = "";
                $filter_def = "";
                //TODO: filter data when available in UI Data Table
                if ($filter_data) {

                }

                $data = $this->glossary->getTermList(
                    $filter_term,
                    "",
                    $filter_def,
                    $this->tax_node,
                    true,
                    true,
                    null,
                    false,
                    true
                );
                $term_ids = [];
                foreach ($data as $term) {
                    $term_ids[$term["id"]] = new \ILIAS\AdvancedMetaData\Services\SubObjectID(
                        $this->glossary->getId(),
                        (int) $term["id"],
                        "term"
                    );
                }
                $adv_md_data = $this->adv_term_mode->getData(...$term_ids);

                $records = [];
                $i = 0;
                foreach ($data as $term) {
                    //TODO: Check if we need all these setParameterByClass calls
                    $term_id = (int) $term["id"];
                    $this->ctrl->setParameterByClass("ilobjglossarygui", "term_id", $term_id);
                    $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $term_id);
                    $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term_id);
                    $records[$i]["term_id"] = $term_id;

                    // text
                    $short_str = \ilGlossaryTerm::_lookShortText($term_id);

                    if (\ilGlossaryTerm::_lookShortTextDirty($term_id)) {
                        // #18022
                        $term_obj = new \ilGlossaryTerm($term_id);
                        $term_obj->updateShortText();
                        $short_str = $term_obj->getShortText();
                    }

                    $page = new \ilGlossaryDefPage($term_id);

                    // replace tex
                    // if a tex end tag is missing a tex end tag
                    $ltexs = strrpos($short_str, "[tex]");
                    $ltexe = strrpos($short_str, "[/tex]");
                    if ($ltexs > $ltexe) {
                        $page->buildDom();
                        $short_str = $page->getFirstParagraphText();
                        $short_str = strip_tags($short_str, "<br>");
                        $ltexe = strpos($short_str, "[/tex]", $ltexs);
                        $short_str = \ilStr::shortenTextExtended($short_str, $ltexe + 6, true);
                    }

                    $short_str = \ilMathJax::getInstance()->insertLatexImages($short_str);

                    $short_str = \ilPCParagraph::xml2output(
                        $short_str,
                        false,
                        true,
                        !$page->getPageConfig()->getPreventHTMLUnmasking()
                    );

                    $records[$i]["definitions"] = $short_str;

                    $this->ctrl->setParameterByClass("ilobjglossarygui", "term_id", $term_id);


                    // usage
                    $nr_usage = \ilGlossaryTerm::getNumberOfUsages($term_id);
                    if ($nr_usage > 0 && $this->glossary->getId() == $term["glo_id"]) {
                        $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $term_id);
                        $records[$i]["usage"] = \ilGlossaryTerm::getNumberOfUsages($term_id);
                        $records[$i]["usage_link"] = $this->ui_fac->link()->standard(
                            $this->lng->txt("link_to_usages"),
                            $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listUsages")
                        );
                        $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", "");
                    } else {
                        $records[$i]["usage"] = \ilGlossaryTerm::getNumberOfUsages($term_id);
                    }

                    // glossary title
                    if ($this->glossary->getVirtualMode() === "coll"
                        || \ilGlossaryTermReferences::hasReferences($this->glossary->getId())
                    ) {
                        $glo_title = \ilObject::_lookupTitle($term["glo_id"]);
                        $records[$i]["glossary"] = $glo_title;
                    }

                    // output language
                    $records[$i]["language"] = $this->lng->txt("meta_l_" . $term["language"]);

                    // advanced metadata
                    $sub_obj_id = $term_ids[$term_id];
                    $adv_data = $adv_md_data->dataForSubObject($sub_obj_id);
                    foreach ($adv_data as $key => $val) {
                        $records[$i][$key] = $val;
                    }

                    foreach ($this->adv_cols_order as $c) {
                        if ($c["id"] == 0) {
                            $records[$i]["term"] = $term["term"];
                        }
                    }

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
