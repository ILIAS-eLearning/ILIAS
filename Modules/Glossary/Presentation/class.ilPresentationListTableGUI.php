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

/**
 * Term list table for presentation mode
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPresentationListTableGUI: ilFormPropertyDispatchGUI
 */
class ilPresentationListTableGUI extends ilTable2GUI
{
    protected ilAdvancedMDRecordGUI $record_gui;
    protected array $adv_fields;
    protected \ILIAS\Glossary\Presentation\PresentationGUIRequest $request;
    protected int $tax_id;
    protected int $tax_node;
    protected ilObjGlossary $glossary;
    protected array $adv_cols_order = array();
    protected bool $offline;
    protected ilPageConfig $page_config;
    protected array $filter = [];

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjGlossary $a_glossary,
        bool $a_offline,
        int $a_tax_node,
        int $a_tax_id = 0
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->glossary = $a_glossary;
        $this->offline = $a_offline;
        $this->tax_node = $a_tax_node;
        $this->tax_id = $a_tax_id;
        $this->setId("glopr" . $this->glossary->getId());
        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->presentation()
            ->request();

        $gdf = new ilGlossaryDefPage();
        $this->page_config = $gdf->getPageConfig();

        $adv_ad = new ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_fields = $adv_ad->getAllFields();


        parent::__construct($a_parent_obj, $a_parent_cmd);
        //$this->setTitle($this->lng->txt("cont_terms"));

        if ($this->glossary->getPresentationMode() == "full_def") {
            $this->addColumn($this->lng->txt("cont_terms"));
        } else {
            $adv_ap = new ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
            $this->adv_cols_order = $adv_ap->getColumnOrder();
            foreach ($this->adv_cols_order as $c) {
                if ($c["id"] == 0) {
                    $this->addColumn($this->lng->txt("cont_term"), "term");
                } else {
                    $this->addColumn($c["text"], "md_" . $c["id"]);
                }
            }


            $this->addColumn($this->lng->txt("cont_definitions"));
            if ($this->glossary->isVirtual()) {
                $this->addColumn($this->lng->txt("obj_glo"));
            }
        }

        $this->setEnableHeader(true);
        if (!$this->offline) {
            $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
        } else {
            $this->disable("footer");
        }
        $this->setRowTemplate("tpl.term_tbl_pres_row.html", "Modules/Glossary");
        $this->setEnableTitle(true);

        if (!$this->offline) {
            $this->initFilter();
            $this->setFilterCommand("applyFilter");

            $this->setShowRowsSelector(true);
        }

        // advanced metadata
        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_FILTER,
            'glo',
            $this->glossary->getId(),
            'term'
        );
        $this->record_gui->setTableGUI($this);
        $this->record_gui->parse();
        //$this->setDefaultOrderField("login");
        //$this->setDefaultOrderDirection("asc");
        $this->setData($this->glossary->getTermList(
            $this->filter["term"] ?? "",
            $this->request->getLetter(),
            $this->filter["definition"] ?? "",
            $this->tax_node,
            false,
            true,
            $this->record_gui->getFilterElements(),
            false,
            true
        ));
        if ($this->offline) {
            $this->setLimit(count($this->getData()));
            $this->resetOffset();
        }
        //		$this->setData(array());
    }
    
    protected function getAdvMDRecordGUI() : ilAdvancedMDRecordGUI
    {
        return $this->record_gui;
    }
    
    public function initFilter() : void
    {
        // term
        $ti = new ilTextInputGUI($this->lng->txt("cont_term"), "term");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["term"] = $ti->getValue();
        
        // definition
        if ($this->glossary->supportsLongTextQuery()) {
            $ti = new ilTextInputGUI($this->lng->txt("cont_definition"), "defintion");
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $ti->setSubmitFormOnEnter(true);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["definition"] = $ti->getValue();
        }
    }
    
    public function numericOrdering(string $a_field) : bool
    {
        if (strpos($a_field, "md_") === 0) {
            $md_id = (int) substr($a_field, 3);
            if ($this->adv_fields[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE) {
                return true;
            }
        }
        return false;
    }

    protected function fillRow(array $a_set) : void
    {
        $defs = ilGlossaryDefinition::getDefinitionList($a_set["id"]);
        $this->ctrl->setParameter($this->parent_obj, "term_id", $a_set["id"]);

        if ($this->glossary->getPresentationMode() == "full_def") {
            $this->tpl->setCurrentBlock("fd_td");
            $this->tpl->setVariable(
                "FULL_DEF",
                $this->parent_obj->listDefinitions(
                    $this->request->getRefId(),
                    $a_set["id"],
                    true
                )
            );
            $this->tpl->parseCurrentBlock();
        } else {
            if (count($defs)) {
                for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
                    $def = $defs[$j];
                    if (count($defs) > 1) {
                        if (!$this->offline) {
                            $this->ctrl->setParameter($this->parent_obj, "term_id", $a_set["id"]);
                            $def_href = $this->ctrl->getLinkTarget($this->parent_obj, "listDefinitions");
                            $this->ctrl->clearParameters($this->parent_obj);
                        } else {
                            $def_href = "term_" . $a_set["id"] . ".html";
                        }
                        $this->tpl->parseCurrentBlock();

                        $this->tpl->setCurrentBlock("definition");
                        $this->tpl->setVariable("DEF_TEXT", $this->lng->txt("cont_definition") . " " . ($j + 1));
                        $this->tpl->setVariable("HREF_DEF", $def_href . "#ilPageTocDef" . ($j + 1));
                        $this->tpl->parseCurrentBlock();
                    }

                    // check dirty short texts
                    $this->tpl->setCurrentBlock("definition");
                    if ($def["short_text_dirty"]) {
                        // #18022
                        $def_obj = new ilGlossaryDefinition($def["id"]);
                        $def_obj->updateShortText();
                        $short_str = $def_obj->getShortText();
                    } else {
                        $short_str = $def["short_text"];
                    }

                    if (!$this->page_config->getPreventHTMLUnmasking()) {
                        $short_str = str_replace(["&lt;", "&gt;"], ["<", ">"], $short_str);
                    }

                    // replace tex
                    // if a tex end tag is missing a tex end tag
                    $ltexs = strrpos($short_str, "[tex]");
                    $ltexe = strrpos($short_str, "[/tex]");
                    if ($ltexs > $ltexe) {
                        $page = new ilGlossaryDefPage($def["id"]);
                        $page->buildDom();
                        $short_str = $page->getFirstParagraphText();
                        $short_str = strip_tags($short_str, "<br>");
                        $ltexe = strpos($short_str, "[/tex]", $ltexs);
                        $short_str = ilStr::shortenTextExtended($short_str, $ltexe + 6, true);
                    }

                    if (!$this->offline) {
                        $short_str = ilMathJax::getInstance()->insertLatexImages($short_str);
                    } else {
                        $short_str = ilMathJax::getInstance()->insertLatexImages(
                            $short_str,
                            '[tex]',
                            '[/tex]'
                        );
                    }

                    $short_str = ilPCParagraph::xml2output($short_str, false, true, false);

                    $this->tpl->setVariable("DEF_SHORT", $short_str);
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("definition_row");
                    $this->tpl->parseCurrentBlock();
                }
            } else {
                $this->tpl->touchBlock("def_td");
            }

            // display additional column 'glossary' for meta glossaries
            if ($this->glossary->isVirtual()) {
                $this->tpl->setCurrentBlock("glossary_row");
                $glo_title = ilObject::_lookupTitle($a_set["glo_id"]);
                $this->tpl->setVariable("GLO_TITLE", $glo_title);
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->ctrl->clearParameters($this->parent_obj);

        // advanced metadata
        foreach ($this->adv_cols_order as $c) {
            if ($c["id"] == 0) {
                $this->tpl->setCurrentBlock("link_start");
                if (!$this->offline) {
                    $this->ctrl->setParameter($this->parent_obj, "term_id", $a_set["id"]);
                    $this->tpl->setVariable(
                        "LINK_VIEW_TERM",
                        $this->ctrl->getLinkTarget($this->parent_obj, "listDefinitions")
                    );

                    $this->ctrl->clearParameters($this->parent_obj);
                } else {
                    $this->tpl->setVariable("LINK_VIEW_TERM", "term_" . $a_set["id"] . ".html");
                }
                $this->tpl->parseCurrentBlock();
                
                $this->tpl->setCurrentBlock("link_end");
                $this->tpl->setVariable("ANCHOR_TERM", "term_" . $a_set["id"]);
                $this->tpl->parseCurrentBlock();
                
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TEXT", $a_set["term"]);
            } else {
                $id = $c["id"];
                                
                $val = " ";
                if (isset($a_set["md_" . $id . "_presentation"])) {
                    $pb = $a_set["md_" . $id . "_presentation"]->getList();
                    if ($pb) {
                        $val = $pb;
                    }
                }
                
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TEXT", $val);
            }
            $this->tpl->parseCurrentBlock();
        }
    }
}
