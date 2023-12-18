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
use ILIAS\Glossary\Presentation\PresentationGUIRequest;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class PresentationListTable
{
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected PresentationGUIRequest $pres_gui_request;
    protected \ilObjGlossary $glossary;
    protected bool $offline;
    protected int $tax_node;
    protected \ilPageConfig $page_config;
    protected array $adv_cols_order = [];
    protected \ILIAS\AdvancedMetaData\Services\SubObjectModes\DataTableInterface $adv_term_mode;

    public function __construct(\ilObjGlossary $glossary, bool $offline, int $tax_node)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->pres_gui_request = $DIC->glossary()->internal()->gui()->presentation()->request();
        $this->glossary = $glossary;
        $this->offline = $offline;
        $this->tax_node = $tax_node;

        $gdf = new \ilGlossaryDefPage();
        $this->page_config = $gdf->getPageConfig();

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
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("cont_terms"), $columns, $data_retrieval)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [];

        $adv_ap = new \ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_cols_order = $adv_ap->getColumnOrder();
        foreach ($this->adv_cols_order as $c) {
            $id = $c["id"];
            if ($id == 0) {
                $columns["term"] = $this->ui_fac->table()->column()->link($this->lng->txt("cont_term"));
            } else {
                $adv_columns = $this->adv_term_mode->getColumns();
                $columns = array_merge($columns, $adv_columns);
            }
        }

        $columns["definitions"] = $this->ui_fac->table()->column()->text($this->lng->txt("cont_definitions"))
                                               ->withIsSortable(false);
        if ($this->glossary->isVirtual()) {
            $columns["glossary"] = $this->ui_fac->table()->column()->text($this->lng->txt("obj_glo"))
                                                ->withIsSortable(false);
        }

        if ($this->offline) {
            $offline_columns = [];
            foreach ($columns as $k => $column) {
                $column = $column->withIsSortable(false);
                $offline_columns[$k] = $column;
            }
            $columns = $offline_columns;
        }

        return $columns;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->glossary,
            $this->offline,
            $this->tax_node,
            $this->pres_gui_request,
            $this->page_config,
            $this->adv_cols_order,
            $this->adv_term_mode,
            $this->ui_fac,
            $this->df
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            protected \ilCtrl $ctrl;

            public function __construct(
                protected \ilObjGlossary $glossary,
                protected bool $offline,
                protected int $tax_node,
                protected PresentationGUIRequest $pres_gui_request,
                protected \ilPageConfig $page_config,
                protected array $adv_cols_order,
                protected \ILIAS\AdvancedMetaData\Services\SubObjectModes\DataTableInterface $adv_term_mode,
                protected UI\Factory $ui_fac,
                protected Data\Factory $df
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
                    $row_id = (string) $record["term_id"];

                    yield $row_builder->buildDataRow($row_id, $record);
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
                if (!$this->offline) {
                    //TODO: filter data when available in UI Data Table
                    if ($filter_data) {

                    }
                }

                // advanced metadata
                //TODO: filter advanced metadata when available in UI Data Table
                /*
                $adv_record_gui = new \ilAdvancedMDRecordGUI(
                    \ilAdvancedMDRecordGUI::MODE_FILTER,
                    "glo",
                    $this->glossary->getId(),
                    "term"
                );
                $adv_record_gui->setTableGUI($this);
                $adv_record_gui->parse();
                $adv_filter_elements = $adv_record_gui->getFilterElements();
                */
                $adv_filter_elements = [];

                $data = $this->glossary->getTermList(
                    $filter_term,
                    $this->pres_gui_request->getLetter(),
                    $filter_def,
                    $this->tax_node,
                    false,
                    true,
                    $adv_filter_elements,
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
                $this->adv_term_mode->loadData(...$term_ids);

                $records = [];
                $i = 0;
                foreach ($data as $term) {
                    $term_id = (int) $term["id"];
                    $this->ctrl->setParameterByClass("ilglossarypresentationgui", "term_id", $term_id);
                    $records[$i]["term_id"] = $term_id;

                    if (\ilGlossaryTerm::_lookShortTextDirty($term_id)) {
                        // #18022
                        $term_obj = new \ilGlossaryTerm($term_id);
                        $term_obj->updateShortText();
                        $short_str = $term_obj->getShortText();
                    } else {
                        $short_str = \ilGlossaryTerm::_lookShortText($term_id);
                    }

                    if (!$this->page_config->getPreventHTMLUnmasking()) {
                        $short_str = str_replace(["&lt;", "&gt;"], ["<", ">"], $short_str);
                    }

                    // replace tex
                    // if a tex end tag is missing a tex end tag
                    $ltexs = strrpos($short_str, "[tex]");
                    $ltexe = strrpos($short_str, "[/tex]");
                    if ($ltexs > $ltexe) {
                        $page = new \ilGlossaryDefPage($term_id);
                        $page->buildDom();
                        $short_str = $page->getFirstParagraphText();
                        $short_str = strip_tags($short_str, "<br>");
                        $ltexe = strpos($short_str, "[/tex]", $ltexs);
                        $short_str = \ilStr::shortenTextExtended($short_str, $ltexe + 6, true);
                    }

                    if (!$this->offline) {
                        $short_str = \ilMathJax::getInstance()->insertLatexImages($short_str);
                    } else {
                        $short_str = \ilMathJax::getInstance()->insertLatexImages(
                            $short_str,
                            '[tex]',
                            '[/tex]'
                        );
                    }

                    $short_str = \ilPCParagraph::xml2output($short_str, false, true, false);

                    $records[$i]["definitions"] = $short_str;

                    // display additional column 'glossary' for meta glossaries
                    if ($this->glossary->isVirtual()) {
                        $glo_title = \ilObject::_lookupTitle($term["glo_id"]);
                        $records[$i]["glossary"] = $glo_title;
                    }

                    $this->ctrl->clearParametersByClass("ilglossarypresentationgui");

                    // advanced metadata
                    $sub_obj_id = $term_ids[$term_id];
                    $adv_data = $this->adv_term_mode->getData($sub_obj_id);
                    foreach ($adv_data as $key => $val) {
                        $records[$i][$key] = $val;
                    }

                    foreach ($this->adv_cols_order as $c) {
                        if ($c["id"] == 0) {
                            if (!$this->offline) {
                                $this->ctrl->setParameterByClass("ilglossarypresentationgui", "term_id", $term_id);
                                $link = $this->ui_fac->link()->standard(
                                    $term["term"],
                                    $this->ctrl->getLinkTargetByClass("ilglossarypresentationgui", "listDefinitions")
                                );
                                $records[$i]["term"] = $link;
                                $this->ctrl->clearParametersByClass("ilglossarypresentationgui");
                            } else {
                                $link = $this->ui_fac->link()->standard(
                                    $term["term"],
                                    "term_" . $term_id . ".html"
                                );
                                $records[$i]["term"] = $link;
                            }
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

                //TODO: HTML export needs to show all records in one view. Currently not possible in UI Data Table, because default is always 5 records
                /*
                if ($this->offline) {
                    $this->setLimit(count($this->getData()));
                    $this->resetOffset();
                }
                */

                return $records;
            }
        };

        return $data_retrieval;
    }
}
