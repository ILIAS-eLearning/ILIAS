<?php

require_once("./Services/Object/classes/class.ilObjectListGUI.php");
class ilObjIndividualAssessmentListGUI extends ilObjectListGUI
{

    /**
    * initialisation
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
        $this->type = "iass";
        $this->gui_class_name = "ilobjIndividualassessmentgui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        $this->enableSubstitutions($this->substitutions->isActive());

        // general commands array
        include_once('./Modules/IndividualAssessment/classes/class.ilObjIndividualAssessmentAccess.php');
        $this->commands = ilObjIndividualAssessmentAccess::_getCommands();
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
        parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
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
            default:
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;
        }

        return $frame;
    }

    public function getCommandLink($a_cmd)
    {
        switch ($a_cmd) {
            case 'edit':
                $return = $this->ctrl->getLinkTargetByClass(array($this->gui_class_name,'ilIndividualassessmentsettingsgui'), "edit");
                break;
            case 'infoScreen':
                $return = $this->ctrl->getLinkTargetByClass($this->gui_class_name, "view");
                break;
            default:
                $return = parent::getCommandLink($a_cmd);
        }

        return $return;
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
        return [];
    }
}
