<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjGlossaryListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @ingroup ModulesGlossary
 */
class ilObjGlossaryListGUI extends ilObjectListGUI
{

    /**
    * initialisation
    *
    * this method should be overwritten by derived classes
    */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "glo";
        $this->gui_class_name = "ilobjglossarygui";
        
        // general commands array
        include_once("./Modules/Glossary/classes/class.ilObjGlossaryAccess.php");
        $this->commands = ilObjGlossaryAccess::_getCommands();
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
        switch ($a_cmd) {
            case "view":
                $cmd_link = "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "properties":
                $this->ctrl->setParameterByClass("ilobjglossarygui", "ref_id", $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui"), $a_cmd);
                break;

            case "infoScreen":
                $cmd_link = "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=infoScreen&amp;ref_id=" . $this->ref_id;
                break;
                
            default:
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
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
            case "edit":
            case "properties":
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

        $props = array();

        include_once("./Modules/Glossary/classes/class.ilObjGlossaryAccess.php");

        if (!ilObjGlossaryAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props;
    }
} // END class.ilObjCategoryGUI
