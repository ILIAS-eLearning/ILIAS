<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Term list table for presentation mode
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGlossary
 * @ilCtrl_Calls ilPresentationListTableGUI: ilFormPropertyDispatchGUI
 */
class ilPresentationListTableGUI extends ilTable2GUI
{

    /**
     * @var ilObjGlossary
     */
    protected $glossary;

    /**
     * @var array
     */
    protected $adv_cols_order = array();

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilPageConfig
     */
    protected $page_config;

    /**
     * Constructor
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_glossary,
        $a_offline,
        $a_tax_node,
        $a_tax_id = 0
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->glossary = $a_glossary;
        $this->offline = $a_offline;
        $this->tax_node = $a_tax_node;
        $this->tax_id = $a_tax_id;
        $this->setId("glopr" . $this->glossary->getId());

        $gdf = new ilGlossaryDefPage();
        $this->page_config = $gdf->getPageConfig();

        include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
        $adv_ad = new ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_fields = $adv_ad->getAllFields();

        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        //$this->setTitle($this->lng->txt("cont_terms"));

        if ($this->glossary->getPresentationMode() == "full_def") {
            $this->addColumn($this->lng->txt("cont_terms"));
        } else {
            include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
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
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_FILTER, 'glo', $this->glossary->getId(), 'term');
        $this->record_gui->setTableGUI($this);
        $this->record_gui->parse();
        //$this->setDefaultOrderField("login");
        //$this->setDefaultOrderDirection("asc");
        $this->setData($this->glossary->getTermList(
            $this->filter["term"],
            $_GET["letter"],
            $this->filter["definition"],
            $this->tax_node,
            false,
            true,
            $this->record_gui->getFilterElements(),
            false,
            true
        ));
        //		$this->setData(array());
    }
    
    /**
     * needed for advmd filter handling
     *
     * @return ilAdvancedMDRecordGUI
     */
    protected function getAdvMDRecordGUI()
    {
        return $this->record_gui;
    }
    
    /**
     * Init filter
     */
    public function initFilter()
    {
        // term
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($this->lng->txt("cont_term"), "term");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["term"] = $ti->getValue();
        
        // definition
        if ($this->glossary->supportsLongTextQuery()) {
            include_once("./Services/Form/classes/class.ilTextInputGUI.php");
            $ti = new ilTextInputGUI($this->lng->txt("cont_definition"), "defintion");
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $ti->setSubmitFormOnEnter(true);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["definition"] = $ti->getValue();
        }
    }
    
    /**
     * Should this field be sorted numeric?
     *
     * @return	boolean		numeric ordering; default is false
     */
    public function numericOrdering($a_field)
    {
        if (substr($a_field, 0, 3) == "md_") {
            $md_id = (int) substr($a_field, 3);
            if ($this->adv_fields[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fill table row
     */
    protected function fillRow($term)
    {
        $defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
        $this->ctrl->setParameter($this->parent_obj, "term_id", $term["id"]);

        if ($this->glossary->getPresentationMode() == "full_def") {
            $this->tpl->setCurrentBlock("fd_td");
            $this->tpl->setVariable(
                "FULL_DEF",
                $this->parent_obj->listDefinitions($_GET["ref_id"], $term["id"], true)
            );
            $this->tpl->parseCurrentBlock();
        } else {
            if (sizeof($defs)) {
                for ($j = 0; $j < count($defs); $j++) {
                    $def = $defs[$j];
                    if (count($defs) > 1) {
                        if (!$this->offline) {
                            if (!empty($filter)) {
                                $this->ctrl->setParameter($this->parent_obj, "term", $filter);
                                $this->ctrl->setParameter($this->parent_obj, "oldoffset", $_GET["oldoffset"]);
                            }
                            $this->ctrl->setParameter($this->parent_obj, "term_id", $term["id"]);
                            $this->ctrl->setParameter($this->parent_obj, "offset", $_GET["offset"]);
                            $def_href = $this->ctrl->getLinkTarget($this->parent_obj, "listDefinitions");
                            $this->ctrl->clearParameters($this->parent_obj);
                        } else {
                            $def_href = "term_" . $term["id"] . ".html";
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
                        $short_str = str_replace("&lt;", "<", $short_str);
                        $short_str = str_replace("&gt;", ">", $short_str);
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
                        $short_str = ilUtil::shortenText($short_str, $ltexe + 6, true);
                    }

                    include_once './Services/MathJax/classes/class.ilMathJax.php';
                    if (!$this->offline) {
                        $short_str = ilMathJax::getInstance()->insertLatexImages($short_str);
                    } else {
                        $short_str = ilMathJax::getInstance()->insertLatexImages(
                            $short_str,
                            '[tex]',
                            '[/tex]',
                            $this->parent_obj->getOfflineDirectory() . '/teximg',
                            './teximg'
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
                $glo_title = ilObject::_lookupTitle($term["glo_id"]);
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
                    if (!empty($filter)) {
                        $this->ctrl->setParameter($this->parent_obj, "term", $filter);
                        $this->ctrl->setParameter($this->parent_obj, "oldoffset", $_GET["oldoffset"]);
                    }
                    $this->ctrl->setParameter($this->parent_obj, "term_id", $term["id"]);
                    $this->ctrl->setParameter($this->parent_obj, "offset", $_GET["offset"]);
                    $this->tpl->setVariable(
                        "LINK_VIEW_TERM",
                        $this->ctrl->getLinkTarget($this->parent_obj, "listDefinitions")
                    );
                    $this->ctrl->clearParameters($this->parent_obj);
                } else {
                    $this->tpl->setVariable("LINK_VIEW_TERM", "term_" . $term["id"] . ".html");
                }
                $this->tpl->parseCurrentBlock();
                
                $this->tpl->setCurrentBlock("link_end");
                $this->tpl->setVariable("ANCHOR_TERM", "term_" . $term["id"]);
                $this->tpl->parseCurrentBlock();
                
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TEXT", $term["term"]);
                $this->tpl->parseCurrentBlock();
            } else {
                $id = $c["id"];
                                
                $val = " ";
                if (isset($term["md_" . $id . "_presentation"])) {
                    $pb = $term["md_" . $id . "_presentation"]->getList();
                    if ($pb) {
                        $val = $pb;
                    }
                }
                
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TEXT", $val);
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
