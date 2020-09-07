<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjFileBasedLMListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @ingroup ModulesHTMLLearningModule
 */
class ilObjFileBasedLMListGUI extends ilObjectListGUI
{
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
        $this->type = "htlm";
        $this->gui_class_name = "ilobjfilebasedlmgui";
        
        // general commands array
        include_once('Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php');
        $this->commands = ilObjFileBasedLMAccess::_getCommands();
    }

    /**
    * Overwrite this method, if link target is not build by ctrl class
    * (e.g. "forum.php"). This is the case
    * for all links now, but bringing everything to ilCtrl should
    * be realised in the future.
    *
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_cmd) {
            case "view":
                $cmd_link = "ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->ref_id;
                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilHTLMEditorGUI&ref_id=" . $this->ref_id;
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
        switch ($a_cmd) {
            case "view":
                $frame = "ilContObj" . $this->obj_id;
                break;

            case "edit":
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
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;

        // centralized offline status
        $props = parent::getProperties();

        if (!ilObjFileBasedLMAccess::_determineStartUrl($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("no_start_file"));
        }

        if ($rbacsystem->checkAccess("write", $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("htlm"));
        }

        return $props;
    }
} // END class.ilObjCategoryGUI
