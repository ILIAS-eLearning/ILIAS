<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");

/**
* Class ilSCORM2004PageNodeGUI
*
* User Interface for Scorm 2004 Page Nodes
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilSCORM2004PageNodeGUI: ilSCORM2004PageGUI, ilAssGenFeedbackPageGUI
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004PageNodeGUI extends ilSCORM2004NodeGUI
{

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_slm_obj, $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "obj_id");
        
        parent::__construct($a_slm_obj, $a_node_id);
    }

    /**
    * Get Node Type
    */
    public function getType()
    {
        return "page";
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
                
            case "ilscorm2004pagegui":
                $tpl->getStandardTemplate();
                $this->setContentStyle();
                $this->setLocator();
                // Determine whether the view of a learning resource should
                // be shown in the frameset of ilias, or in a separate window.
                $showViewInFrameset = true;

                $ilCtrl->setReturn($this, "edit");
                include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php");
                $page_gui = new ilSCORM2004PageGUI(
                    $this->slm_object->getType(),
                    $this->node_object->getId(),
                    0,
                    $this->getParentGUI()->object->getId(),
                    $this->slm_object->getAssignedGlossary()
                );
                $page_gui->setEditPreview(true);
                $page_gui->setPresentationTitle($this->node_object->getTitle());
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->slm_object->getStyleSheetId(),
                    "sahs"
                ));

                if ($this->node_object->tree->getParentId($this->node_object->getId()) > 0) {
                    $sco = new ilSCORM2004Sco(
                        $this->node_object->getSLMObject(),
                        $this->node_object->tree->getParentId(
                            $this->node_object->getId()
                        )
                    );
                    if (count($sco->getGlossaryTermIds()) > 1) {
                        include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
                        $page_gui->setGlossaryOverviewInfo(
                            ilSCORM2004ScoGUI::getGlossaryOverviewId(),
                            $sco
                        );
                    }
                }
                
                $ilCtrl->setParameterByClass(
                    "ilobjscorm2004learningmodulegui",
                    "active_node",
                    $_GET["obj_id"]
                );
                $page_gui->setExplorerUpdater(
                    "tree",
                    "tree_div",
                    $ilCtrl->getLinkTargetByClass(
                        "ilobjscorm2004learningmodulegui",
                        "showTree",
                        "",
                        true
                    )
                );
                $ilCtrl->setParameterByClass(
                    "ilobjscorm2004learningmodulegui",
                    "active_node",
                    ""
                );

                // set page view link
                $view_frame = ilFrameTargetInfo::_getFrame("MainContent");
                $page_gui->setLinkParams("ref_id=" . $this->slm_object->getRefId());
                $tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
                
                $page_gui->activateMetaDataEditor(
                    $this->slm_object,
                    $this->node_object->getType(),
                    $this->node_object->getId(),
                    $this->node_object,
                    'MDUpdateListener'
                );
                
                $ret = $ilCtrl->forwardCommand($page_gui);
                $this->setTabs();
                $tpl->setContent($ret);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
    * Edit -> switch to ilscorm2004pagegui
    */
    public function edit()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setCmdClass("ilscorm2004pagegui");
        $ilCtrl->setCmd("edit");
        $this->executeCommand();
    }
    
    /**
    * output tabs
    */
    public function setTabs()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        // metadata
        /*		$ilTabs->addTarget("meta_data",
                     $ilCtrl->getLinkTargetByClass("ilmdeditorgui",''),
                     "", "ilmdeditorgui");*/
             
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
        $tpl->setTitle(
            $lng->txt("sahs_page") . ": " . $this->node_object->getTitle()
        );
    }
}
