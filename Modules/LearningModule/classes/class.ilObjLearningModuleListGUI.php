<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjLearningModuleListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilObjLearningModuleListGUI extends ilObjectListGUI
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
        $this->type = "lm";
        $this->gui_class_name = "ilobjlearningmodulegui";
        
        // general commands array
        include_once('./Modules/LearningModule/classes/class.ilObjLearningModuleAccess.php');
        $this->commands = ilObjLearningModuleAccess::_getCommands();
    }

    public function setChildId($a_child_id)
    {
        $this->child_id = $a_child_id;
    }
    public function getChildId()
    {
        return $this->child_id;
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
            case "continue":
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=resume";
                break;

            case "page":
                // Used for presentation of single pages chapters in search results
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;obj_id=" . $this->getChildId();
                break;

            case "view":
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilLMEditorGUI&amp;ref_id=" . $this->ref_id;
                break;
                
            case "properties":
                $cmd_link = "ilias.php?baseClass=ilLMEditorGUI&amp;ref_id=" . $this->ref_id . "&amp;to_props=1";
                break;
                
            case "infoScreen":
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=infoScreen&amp;file_id" . $this->getChildId();
                break;
                
            case 'downloadFile':
                $cmd_link = 'ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=' . $this->ref_id .
                    '&amp;cmd=downloadFile&amp;file_id=' . $this->getChildId();
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
            case "continue":
            case 'list':
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            case "edit":
            case "properties":
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
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;

        $props = parent::getProperties();

        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("lm"));
        }

        return $props;
    }

    /**
    * Get command icon image
    */
    public function getCommandImage($a_cmd)
    {
        switch ($a_cmd) {
            default:
                return "";
        }
    }
} // END class.ilObjCategoryGUI
