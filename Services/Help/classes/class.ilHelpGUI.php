<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

/**
 * Help GUI class.
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilHelpGUI: ilLMPageGUI
 *
 */
class ilHelpGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    public $help_sections = array();
    const ID_PART_SCREEN = "screen";
    const ID_PART_SUB_SCREEN = "sub_screen";
    const ID_PART_COMPONENT = "component";
    public $def_screen_id = array();
    public $screen_id = array();

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ?array
     */
    protected $raw_menu_items = null;

    /**
    * constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
                
        $this->ctrl = $ilCtrl;
        $this->ui = $DIC->ui();
    }
    
    /**
     * Set default screen id
     *
     * @param
     * @return
     */
    public function setDefaultScreenId($a_part, $a_id)
    {
        $this->def_screen_id[$a_part] = $a_id;
    }

    /**
     * Set screen id
     *
     * @param
     */
    public function setScreenId($a_id)
    {
        $this->screen_id[self::ID_PART_SCREEN] = $a_id;
    }

    /**
     * Set sub screen id
     *
     * @param
     */
    public function setSubScreenId($a_id)
    {
        $this->screen_id[self::ID_PART_SUB_SCREEN] = $a_id;
    }

    /**
     * Set screen id component
     *
     * @param
     * @return
     */
    public function setScreenIdComponent($a_comp)
    {
        $this->screen_id_component = $a_comp;
    }
    
    
    /**
     * Get screen id
     *
     * @param
     * @return
     */
    public function getScreenId()
    {
        $comp = ($this->screen_id_component != "")
            ? $this->screen_id_component
            : $this->def_screen_id[self::ID_PART_COMPONENT];
        
        if ($comp == "") {
            return "";
        }
        
        $scr_id = ($this->screen_id[self::ID_PART_SCREEN] != "")
            ? $this->screen_id[self::ID_PART_SCREEN]
            : $this->def_screen_id[self::ID_PART_SCREEN];
        
        $sub_scr_id = ($this->screen_id[self::ID_PART_SUB_SCREEN] != "")
            ? $this->screen_id[self::ID_PART_SUB_SCREEN]
            : $this->def_screen_id[self::ID_PART_SUB_SCREEN];
        
        $screen_id = $comp . "/" .
            $scr_id . "/" .
            $sub_scr_id;
            
        return $screen_id;
    }
    
    
    /**
     * Add help section
     *
     * @param
     * @return
     */
    public function addHelpSection($a_help_id, $a_level = 1)
    {
        $this->help_sections[] = array("help_id" => $a_help_id, $a_level);
    }
    
    /**
     * Has sections?
     *
     * @param
     * @return
     */
    public function hasSections()
    {
        $ilSetting = $this->settings;
        
        include_once("./Services/Help/classes/class.ilHelpMapping.php");
        return ilHelpMapping::hasScreenIdSections($this->getScreenId());
    }
    
    /**
     * Get help sections
     *
     * @param
     * @return
     */
    public function getHelpSections()
    {
        include_once("./Services/Help/classes/class.ilHelpMapping.php");
        return ilHelpMapping::getHelpSectionsForId($this->getScreenId(), (int) $_GET["ref_id"]);
    }
    
    /**
     * Get help section url parameter
     *
     * @param
     * @return
     */
    public function setCtrlPar()
    {
        $ilCtrl = $this->ctrl;
        
        /*$h_ids = $sep = "";
        foreach ($this->getHelpSections() as $hs)
        {
            $h_ids.= $sep.$hs;
            $sep = ",";
        }*/
        $ilCtrl->setParameterByClass("ilhelpgui", "help_screen_id", $this->getScreenId() . "." . $_GET["ref_id"]);
    }
    

    /**
    * execute command
    */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showHelp");
        $next_class = $this->ctrl->getNextClass($this);
        
        switch ($next_class) {
            default:
                return $this->$cmd();
                break;
        }
    }
    
    /**
     * Show online help
     */
    public function showHelp()
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("help");
        $ui = $this->ui;

        if ($_GET["help_screen_id"] != "") {
            ilSession::set("help_screen_id", $_GET["help_screen_id"]);
            $help_screen_id = $_GET["help_screen_id"];
        } else {
            $help_screen_id = ilSession::get("help_screen_id");
        }

        ilSession::set("help_search_term", "");

        $this->resetCurrentPage();

        $id_arr = explode(".", $help_screen_id);
        include_once("./Services/Help/classes/class.ilHelpMapping.php");
        include_once("./Services/Help/classes/class.ilHelp.php");

        $help_arr = ilHelpMapping::getHelpSectionsForId($id_arr[0], $id_arr[1]);
        $oh_lm_id = ilHelp::getHelpLMId();

        if ($oh_lm_id > 0) {
            include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
            $acc = new ilAccordionGUI();
            $acc->setId("oh_acc_" . $h_id);
            $acc->setUseSessionStorage(true);
            $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

            foreach ($help_arr as $h_id) {
                include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                $st_id = $h_id;
                if (!ilLMObject::_exists($st_id)) {
                    continue;
                }

                $pages = ilLMObject::getPagesOfChapter($oh_lm_id, $st_id);
                include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
                $grp_list = new ilGroupedListGUI();
                foreach ($pages as $pg) {
                    $grp_list->addEntry(
                        $this->replaceMenuItemTags((string) ilLMObject::_lookupTitle($pg["child"])),
                        "#",
                        "",
                        "return il.Help.showPage(" . $pg["child"] . ");"
                    );
                }
                
                $acc->addItem(ilLMObject::_lookupTitle($st_id), $grp_list->getHTML());
            }

            $h_tpl = new ilTemplate("tpl.help.html", true, true, "Services/Help");
            $h_tpl->setVariable("HEAD", $lng->txt("help"));

            $h_tpl->setCurrentBlock("search");
            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            $h_tpl->setVariable("GL_SEARCH", ilGlyphGUI::get(ilGlyphGUI::SEARCH));
            $h_tpl->setVariable("HELP_SEARCH_LABEL", $this->lng->txt("help_search_label"));
            $h_tpl->parseCurrentBlock();

            if (count($help_arr) > 0) {
                $h_tpl->setVariable("CONTENT", $acc->getHTML());
            } else {
                $mess = $ui->factory()->messageBox()->info($lng->txt("help_no_content"));
                $h_tpl->setVariable("CONTENT", $ui->renderer()->render([$mess]));
            }

            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            $h_tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            echo $h_tpl->get();
        }
        exit;
    }
    
    /**
     * Show page
     *
     * @param
     * @return
     */
    public function showPage()
    {
        $lng = $this->lng;
        $ui = $this->ui;
        
        $page_id = (int) $_GET["help_page"];
        
        $h_tpl = new ilTemplate("tpl.help.html", true, true, "Services/Help");
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");


        if (($t = ilSession::get("help_search_term")) != "") {
            $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) use ($t) {
                return
                    "$(\"#$id\").click(function() { return il.Help.search('" . ilUtil::prepareFormOutput($t) . "'); return false;});";
            });
            $h_tpl->setVariable("BACKBUTTON", $ui->renderer()->renderAsync($back_button));
        } else {
            $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) {
                return
                    "$(\"#$id\").click(function() { return il.Help.listHelp(event, true); return false;});";
            });
            $h_tpl->setVariable("BACKBUTTON", $ui->renderer()->renderAsync($back_button));
        }
        
        $h_tpl->setVariable(
            "HEAD",
            $this->replaceMenuItemTags((string) ilLMObject::_lookupTitle($page_id))
        );
        
        include_once("./Services/COPage/classes/class.ilPageUtil.php");
        if (!ilPageUtil::_existsAndNotEmpty("lm", $page_id)) {
            exit;
        }
        include_once("./Services/COPage/classes/class.ilPageObject.php");
        include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

        // get page object
        include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
        include_once("./Modules/LearningModule/classes/class.ilLMPageGUI.php");
        $page_gui = new ilLMPageGUI($page_id);
        $cfg = $page_gui->getPageConfig();
        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $page_gui->setRawPageContent(true);
        $cfg->setEnablePCType("Map", false);
        $cfg->setEnablePCType("Tabs", false);
        $cfg->setEnablePCType("FileList", false);
        
        $page_gui->getPageObject()->buildDom();
        $int_links = $page_gui->getPageObject()->getInternalLinks();
        $link_xml = $this->getLinkXML($int_links);
        $link_xml .= $this->getLinkTargetsXML();
        //echo htmlentities($link_xml);
        $page_gui->setLinkXML($link_xml);
        
        $ret = $this->replaceMenuItemTags((string) $page_gui->showPage());

        $h_tpl->setVariable("CONTENT", $ret);
        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
        $h_tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));

        ilSession::set("help_pg", $page_id);
        
        $page = $h_tpl->get();
        
        // replace style classes
        //$page = str_replace("ilc_text_inline_Strong", "ilHelpStrong", $page);
        
        echo $page;
        exit;
    }
    
    /**
     * Hide help
     *
     * @param
     * @return
     */
    public function resetCurrentPage()
    {
        ilSession::clear("help_pg");
    }
    
    
    /**
     * Get tab tooltip text
     *
     * @param string $a_tab_id tab id
     * @return string tooltip text
     */
    public function getTabTooltipText($a_tab_id)
    {
        $lng = $this->lng;
        
        include_once("./Services/Help/classes/class.ilHelp.php");
        if ($this->screen_id_component != "") {
            return ilHelp::getTooltipPresentationText($this->screen_id_component . "_" . $a_tab_id);
            //return $lng->txt("help_tt_".$this->screen_id_component."_".$a_tab_id);
        }
        return "";
    }
    
    /**
     * Render current help page
     *
     * @param
     * @return
     */
    public function initHelp($a_tpl, $ajax_url)
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilSetting = $DIC->settings();
        $ctrl = $DIC->ctrl();

        ilYuiUtil::initConnection();
        $a_tpl->addJavascript("./Services/Help/js/ilHelp.js");
        $a_tpl->addJavascript("./Services/Accordion/js/accordion.js");
        iljQueryUtil::initMaphilight();
        $a_tpl->addJavascript("./Services/COPage/js/ilCOPagePres.js");

        $this->setCtrlPar();
        $a_tpl->addOnLoadCode(
            "il.Help.setAjaxUrl('" .
            $ctrl->getLinkTargetByClass("ilhelpgui", "", "", true)
            . "');"
        );


        $module_id = (int) $ilSetting->get("help_module");

        if ((OH_REF_ID > 0 || $module_id > 0) && $ilUser->getLanguage() == "de") {
            if (ilSession::get("help_pg") > 0) {
                $a_tpl->addOnLoadCode("il.Help.showCurrentPage(" . ilSession::get("help_pg") . ");", 3);
            } else {
                $a_tpl->addOnLoadCode("il.Help.listHelp(null);", 3);
            }


            if ($ilUser->getPref("hide_help_tt")) {
                $a_tpl->addOnLoadCode("if (il && il.Help) {il.Help.switchTooltips();}", 3);
            }
        }
    }

    /**
     * Is help page active?
     * @param
     * @return
     */
    public function isHelpPageActive()
    {
        return (ilSession::get("help_pg") > 0);
    }

    /**
     * Deactivate tooltips
     *
     * @param
     * @return
     */
    public function deactivateTooltips()
    {
        $ilUser = $this->user;
        
        $ilUser->writePref("hide_help_tt", "1");
    }
    
    /**
     * Activate tooltips
     *
     * @param
     * @return
     */
    public function activateTooltips()
    {
        $ilUser = $this->user;
        
        $ilUser->writePref("hide_help_tt", "0");
    }
    
    /**
     * get xml for links
     */
    public function getLinkXML($a_int_links)
    {
        $ilCtrl = $this->ctrl;

        $link_info = "<IntLinkInfos>";
        foreach ($a_int_links as $int_link) {
            $target = $int_link["Target"];
            if (substr($target, 0, 4) == "il__") {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $int_link["Type"];
                $targetframe = "None";
                    
                // anchor
                $anc = $anc_add = "";
                if ($int_link["Anchor"] != "") {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_" . rawurlencode($int_link["Anchor"]);
                }

                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                            if ($type == "PageObject") {
                                $href = "#pg_" . $target_id;
                            } else {
                                $href = "#";
                            }
                        break;

                }
                
                $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " .
                    "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"\" Anchor=\"\"/>";
            }
        }
        $link_info .= "</IntLinkInfos>";

        return $link_info;
    }

    /**
    * Get XMl for Link Targets
    */
    public function getLinkTargetsXML()
    {
        $link_info = "<LinkTargets>";
        $link_info .= "<LinkTarget TargetFrame=\"None\" LinkTarget=\"\" OnClick=\"return il.Help.openLink(event);\" />";
        $link_info .= "</LinkTargets>";
        return $link_info;
    }

    /**
     * Search
     *
     * @param
     * @return
     */
    public function search()
    {
        $lng = $this->lng;
        $ui = $this->ui;

        $term = $_GET["term"];

        if ($term == "") {
            $term = ilSession::get("help_search_term");
        }

        $this->resetCurrentPage();

        $h_tpl = new ilTemplate("tpl.help.html", true, true, "Services/Help");
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");


        $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) {
            return
                "$(\"#$id\").click(function() { return il.Help.listHelp(event, true); return false;});";
        });
        $h_tpl->setVariable("BACKBUTTON", $ui->renderer()->renderAsync($back_button));

        $h_tpl->setVariable("HEAD", $lng->txt("help") . " - " .
            $lng->txt("search_result"));

        $h_tpl->setCurrentBlock("search");
        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
        $h_tpl->setVariable("GL_SEARCH", ilGlyphGUI::get(ilGlyphGUI::SEARCH));
        $h_tpl->setVariable("HELP_SEARCH_LABEL", $this->lng->txt("help_search_label"));
        $h_tpl->setVariable("VAL_SEARCH", ilUtil::prepareFormOutput($term));
        $h_tpl->parseCurrentBlock();

        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
        $h_tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));

        $lm_id = ilHelp::getHelpLMId();
        include_once("./Services/Search/classes/class.ilRepositoryObjectDetailSearch.php");
        $s = new ilRepositoryObjectDetailSearch($lm_id);
        $s->setQueryString($term);
        $result = $s->performSearch();

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $grp_list = new ilGroupedListGUI();
        foreach ($result->getResults() as $r) {
            $grp_list->addEntry(
                ilLMObject::_lookupTitle($r["item_id"]),
                "#",
                "",
                "return il.Help.showPage(" . $r["item_id"] . ");"
            );
        }
        $h_tpl->setVariable("CONTENT", $grp_list->getHTML());

        ilSession::set("help_search_term", $term);

        echo $h_tpl->get();
        ;
        exit;
    }




    /**
     * Help page post processing
     *
     * @param string $content
     * @return string
     * @throws Throwable
     */
    protected function replaceMenuItemTags(string $content) : string
    {
        global $DIC;

        $mmc = $DIC->globalScreen()->collector()->mainmenu();
        if ($this->raw_menu_items == null) {
            $mmc->collectStructure();
            $this->raw_menu_items = iterator_to_array($mmc->getRawItems());
        }

        foreach ($this->raw_menu_items as $item) {
            if ($item instanceof Item\LinkList) {
                foreach ($item->getLinks() as $link) {
                    $content = $this->replaceItemTag($mmc, $content, $link);
                }
            }
            $content = $this->replaceItemTag($mmc, $content, $item);
        }
        return $content;
    }

    /**
     * Replace item tag
     *
     * @param
     * @return
     */
    protected function replaceItemTag($mmc, string $content, \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem $item)
    {
        global $DIC;
        $mmc = $DIC->globalScreen()->collector()->mainmenu();

        $id = $item->getProviderIdentification()->getInternalIdentifier();
        $ws = "[ \t\r\f\v\n]*";

        // menu item path
        while (preg_match("~\[(menu" . $ws . "path$ws=$ws(\"$id\")$ws)/\]~i", $content, $found)) {
            $path = "";
            if ($item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild) {
                $parent = $mmc->getItemInformation()->getParent($item);
                if ($parent !== null) {
                    $parent = $mmc->getSingleItemFromRaw($parent);
                    $path = $this->getTitleForItem($parent) . " > ";
                }
            }
            $path .= $this->getTitleForItem($item);
            $content = preg_replace(
                '~\[' . $found[1] . '/\]~i',
                "<strong>" . $path . "</strong>",
                $content
            );
        }
        // menu item
        while (preg_match("~\[(menu" . $ws . "item$ws=$ws(\"$id\")$ws)/\]~i", $content, $found)) {
            $content = preg_replace(
                '~\[' . $found[1] . '/\]~i',
                "<strong>" . $this->getTitleForItem($item) . "</strong>",
                $content
            );
        }
        return $content;
    }

    /**
     * Get title for item
     * @param \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem $item
     * @return string
     * @throws Throwable
     */
    protected function getTitleForItem(\ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem $item): string
    {
        global $DIC;
        $mmc = $DIC->globalScreen()->collector()->mainmenu();
        return $mmc->getItemInformation()->customTranslationForUser($item)->getTitle();
    }

}
