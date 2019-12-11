<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");

/**
* GUI class for glossary term definition editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilTermDefinitionEditorGUI: ilGlossaryDefPageGUI
*
* @ingroup ModulesGlossary
*/
class ilTermDefinitionEditorGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    public $tpl;
    public $lng;
    public $glossary;
    public $definition;
    public $term;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->glossary = new ilObjGlossary($_GET["ref_id"], true);
        $this->definition = new ilGlossaryDefinition($_GET["def"]);
        $this->term = new ilGlossaryTerm($this->definition->getTermId());
        $this->term_glossary = new ilObjGlossary(ilGlossaryTerm::_lookGlossaryID($this->definition->getTermId()), false);
        $this->tabs_gui = $ilTabs;

        $this->ctrl->saveParameter($this, array("def"));
    }


    public function executeCommand()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->term_glossary->getStyleSheetId())
        );
        $this->tpl->parseCurrentBlock();

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();

        require_once("./Modules/Glossary/classes/class.ilGlossaryLocatorGUI.php");
        $gloss_loc = new ilGlossaryLocatorGUI();
        $gloss_loc->setTerm($this->term);
        $gloss_loc->setGlossary($this->glossary);
        $gloss_loc->setDefinition($this->definition);

        //		$this->tpl->getStandardTemplate();
        $this->tpl->setTitle($this->term->getTerm() . " - " .
            $this->lng->txt("cont_definition") . " " .
            $this->definition->getNr());
        if ($this->ctrl->getNextClass() == "ilglossarydefpagegui") {
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
        }

        switch ($next_class) {

            case "ilglossarydefpagegui":
                
                // output number of usages
                if ($ilCtrl->getCmd() == "edit" &&
                    $ilCtrl->getCmdClass() == "ilglossarydefpagegui") {
                    $nr = ilGlossaryTerm::getNumberOfUsages($_GET["term_id"]);
                    if ($nr > 0) {
                        $link = "[<a href='" .
                            $ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listUsages") .
                            "'>" . $lng->txt("glo_list_usages") . "</a>]";
                        ilUtil::sendInfo(sprintf(
                            $lng->txt("glo_term_is_used_n_times"),
                            $nr
                        ) . " " . $link);
                    }
                }
            
                // not so nice, to do: revise locator handling
                if ($this->ctrl->getNextClass() == "ilglossarydefpagegui"
                    || $this->ctrl->getCmdClass() == "ileditclipboardgui") {
                    $gloss_loc->display();
                }
                $this->setTabs();
                $this->ctrl->setReturnByClass("ilGlossaryDefPageGUI", "edit");
                $this->ctrl->setReturn($this, "listDefinitions");
                $page_gui = new ilGlossaryDefPageGUI($this->definition->getId());
                $page = $page_gui->getPageObject();
                $this->definition->assignPageObject($page);
                $page->addUpdateListener($this, "saveShortText");
                $page_gui->setEditPreview(true);
                
                // metadata
                // ... set title to term, if no title is given
                include_once("./Services/MetaData/classes/class.ilMD.php");
                $md = new ilMD($this->term_glossary->getId(), $this->definition->getId(), "gdf");
                $md_gen = $md->getGeneral();
                if ($md_gen->getTitle() == "") {
                    $md_gen->setTitle($this->term->getTerm());
                    $md_gen->update();
                }

                $page_gui->activateMetaDataEditor($this->term_glossary, "gdf", $this->definition->getId());
                
                $page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $_GET["ref_id"]);
                $page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=fullscreen&amp;ref_id=" . $_GET["ref_id"]);
                $page_gui->setTemplateTargetVar("ADM_CONTENT");
                $page_gui->setOutputMode("edit");

                $page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->term_glossary->getStyleSheetId(),
                    "glo"
                ));
                $page_gui->setLocator($gloss_loc);
                $page_gui->setIntLinkReturn($this->ctrl->getLinkTargetByClass(
                    "ilobjglossarygui",
                    "quickList",
                    "",
                    false,
                    false
                ));
                $page_gui->setPageBackTitle($this->lng->txt("cont_definition"));
                $page_gui->setLinkParams("ref_id=" . $_GET["ref_id"]);
                $page_gui->setHeader($this->term->getTerm());
                $page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=" . $_GET["ref_id"]);
                $page_gui->setPresentationTitle($this->term->getTerm());
                $ret = $this->ctrl->forwardCommand($page_gui);
                $tpl->setContent($ret);
                break;

            default:
                $this->setTabs();
                $gloss_loc->display();
                $ret = $this->$cmd();
                break;

        }
    }


    /**
    * output main header (title and locator)
    */
    public function main_header($a_header_title)
    {
        $lng = $this->lng;

        $this->tpl->getStandardTemplate();
        $this->tpl->setTitle($a_header_title);
        $this->displayLocator();
        //$this->setAdminTabs($a_type);
    }

    /**
    * output tabs
    */
    public function setTabs()
    {
        // catch feedback message
        $this->getTabs();
    }

    /**
    * get tabs
    */
    public function getTabs()
    {
        // back to glossary
        $this->tabs_gui->setBack2Target(
            $this->lng->txt("glossary"),
            $this->ctrl->getParentReturn($this)
        );

        // back to upper context
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("term"),
            $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "editTerm")
        );
    }


    public function saveShortText()
    {
        $this->definition->updateShortText();
    }
}
