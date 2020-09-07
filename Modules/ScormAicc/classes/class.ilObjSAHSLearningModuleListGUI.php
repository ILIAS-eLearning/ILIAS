<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjSAHSLearningModuleListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @ingroup ModulesScormAicc
 */
class ilObjSAHSLearningModuleListGUI extends ilObjectListGUI
{
    /**
    * constructor
    *
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * initialisation
    *
    * this method should be overwritten by derived classes
    */
    public function init()
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "sahs";
        $this->gui_class_name = "ilobjsahslearningmodulegui";
        include_once('./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php');
    }

    /**
    * inititialize new item
    *
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	string		$a_title		title
    * @param	string		$a_description	description
    */
    public function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
    {
        // general commands array
        $this->commands = ilObjSAHSLearningModuleAccess::_getCommands($a_obj_id);
        parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
    }

    /**
    * Overwrite this method, if link target is not build by ctrl class
    * (e.g. "lm_presentation.php", "forum.php"). This is the case
    * for all links now, but bringing everything to ilCtrl should
    * be realised in the future.
    *
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $cmd_link = null;
        switch ($a_cmd) {
            case "view":
                require_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php";
                if (!ilObjSAHSLearningModuleAccess::_lookupEditable($this->obj_id)) {
                    if ($this->offline_mode) {
                        $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=offlineModeStart";
                    } else {
                        $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id;
                    }
                } else {
                    $cmd_link = "ilias.php?baseClass=ilSAHSEditGUI&amp;ref_id=" . $this->ref_id;
                }

                break;
            case "offlineModeView":
                $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=offlineModeView";
                break;

            case "editContent":
                $cmd_link = "ilias.php?baseClass=ilSAHSEditGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=editContent";
                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilSAHSEditGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "infoScreen":
                $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=infoScreen";
                break;

            case "offlineModeStart":
                $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=offlineModeStart";
//				$cmd_link = $ilCtrl->getLinkTargetByClass(array('ilsahspresentationgui', 'ilscormofflinemodegui'),'start&amp;ref_id='.$_GET["ref_id"]);
                break;

            case "offlineModeStop":
                $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id . "&amp;cmdClass=ilSCORMOfflineModeGUI&amp;cmd=stop";
                break;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                break;
        }

        return $cmd_link;
    }


    /**
    * Get command target frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        
        switch ($a_cmd) {
            case "view":
                require_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
                $sahs_obj = new ilObjSAHSLearningModule($this->ref_id);
                if ($this->offline_mode) {
                    $frame = ilFrameTargetInfo::_getFrame("MainContent");
                } else {
                    $frame = "ilContObj" . $this->obj_id;
                }
                if ($sahs_obj->getEditable() == 1) {
                    $frame = ilFrameTargetInfo::_getFrame("MainContent");
                }
                break;

            case "edit":
            case "editContent":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;
                
            case "infoScreen":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
                $frame = "";
                break;
        }

        return $frame;
    }


    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $props = parent::getProperties();
        
        $editable = ilObjSAHSLearningModuleAccess::_lookupEditable($this->obj_id);
        
        if ($editable) {
            $props[] = array("alert" => true,
                "value" => $lng->txt("authoring_mode"));
        }

        if ($rbacsystem->checkAccess("write", $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("sahs"));
        }

        $certValidator = new ilCertificateDownloadValidator();
        $allowed = $certValidator->isCertificateDownloadable($this->user->getId(), $this->obj_id);
        if ($allowed) {
            include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
            $type = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
            switch ($type) {
                case "scorm":
                    $lng->loadLanguageModule('certificate');
                    $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id .
                            "&amp;cmd=downloadCertificate";
                    $props[] = array("alert" => false, "property" => $lng->txt("condition_finished"),
                        "value" => '<a href="' . $cmd_link . '">' . $lng->txt("download_certificate") . '</a>');
                    break;
                case "scorm2004":
                    $lng->loadLanguageModule('certificate');
                    $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id .
                            "&amp;cmd=downloadCertificate";
                    $props[] = array("alert" => false, "property" => $lng->txt("condition_finished"),
                        "value" => '<a href="' . $cmd_link . '">' . $lng->txt("download_certificate") . '</a>');
                    break;
            }
        }

        return $props;
    }
} // END class.ilObjCategoryGUI
