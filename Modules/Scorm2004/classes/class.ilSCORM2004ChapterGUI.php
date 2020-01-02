<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");

/**
* Class ilSCORM2004ChapterGUI
*
* User Interface for Scorm 2004 Chapter Nodes
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilSCORM2004ChapterGUI: ilObjectMetaDataGUI, ilNoteGUI
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004ChapterGUI extends ilSCORM2004NodeGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilHelpGUI
     */
    protected $help;


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
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "obj_id");
        
        parent::__construct($a_slm_obj, $a_node_id);
    }

    /**
    * Get Node Type
    */
    public function getType()
    {
        return "chap";
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $tpl->getStandardTemplate();
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            // notes
            case "ilnotegui":
                switch ($_GET["notes_mode"]) {
                    default:
                        return $this->showOrganization();
                }
                break;

            case 'ilobjectmetadatagui':
                $this->setTabs();
                $this->setLocator();
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI(
                    $this->slm_object,
                    $this->node_object->getType(),
                    $this->node_object->getId()
                );
                $md_gui->addMDObserver($this->node_object, 'MDUpdateListener', 'General');
                $ilCtrl->forwardCommand($md_gui);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }
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
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("sahsed");

        // subelements
        $ilTabs->addTarget(
            "sahs_organization",
            $ilCtrl->getLinkTarget($this, 'showOrganization'),
            "showOrganization",
            get_class($this)
        );
        /*
                // properties
                $ilTabs->addTarget("sahs_properties",
                     $ilCtrl->getLinkTarget($this,'showProperties'),
                     "showProperties", get_class($this));
        */
        // metadata
        include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
        $mdgui = new ilObjectMetaDataGUI(
            $this->slm_object,
            $this->node_object->getType(),
            $this->node_object->getId()
        );
        $mdtab = $mdgui->getTab();
        if ($mdtab) {
            $ilTabs->addTarget(
                "meta_data",
                $mdtab,
                "",
                "ilmdeditorgui"
            );
        }
             
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_chap.svg"));
        $tpl->setTitle(
            $lng->txt("sahs_chapter") . ": " . $this->node_object->getTitle()
        );
    }

    /**
    * Show Sequencing
    */
    public function showProperties()
    {
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqTemplate.php");
        
        $tpl = $this->tpl;
        
        $this->setTabs();
        $this->setLocator();
        $tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.scormeditor_chapter_properties.html", "Modules/Scorm2004");
        $template = ilSCORM2004SeqTemplate::templateForChapter($this->node_object->getId());
        if ($template) {
            $item_data = $template->getMetadataProperties();
            $tpl->setVariable("VAL_DESCRIPTION", $item_data['description']);
            $tpl->setVariable("VAL_TITLE", $item_data['title']);
            $tpl->setVariable("VAL_IMAGE", ilSCORM2004SeqTemplate::SEQ_TEMPLATE_DIR . "/images/" . $item_data['thumbnail']);
        } else {
            $tpl->setContent("No didactical scenario assigned.");
        }
    }

    /**
    * Perform drag and drop action
    */
    public function proceedDragDrop()
    {
        $ilCtrl = $this->ctrl;

        $this->slm_object->executeDragDrop(
            $_POST["il_hform_source_id"],
            $_POST["il_hform_target_id"],
            $_POST["il_hform_fc"],
            $_POST["il_hform_as_subitem"]
        );
        $ilCtrl->redirect($this, "showOrganization");
    }
}
