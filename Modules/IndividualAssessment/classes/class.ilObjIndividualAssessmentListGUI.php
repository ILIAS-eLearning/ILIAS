<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilObjIndividualAssessmentListGUI extends ilObjectListGUI
{
    public function init() : void
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
        $this->commands = ilObjIndividualAssessmentAccess::_getCommands();
    }

    /**
    * Get command target frame
    *
    * @param	string		$cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame(string $cmd) : string
    {
        return ilFrameTargetInfo::_getFrame("MainContent");
    }

    public function getCommandLink(string $cmd) : string
    {
        switch ($cmd) {
            case 'edit':
                $return = $this->ctrl->getLinkTargetByClass(
                    array($this->gui_class_name,'ilIndividualassessmentsettingsgui'),
                    "edit"
                );
                break;
            case 'infoScreen':
                $return = $this->ctrl->getLinkTargetByClass($this->gui_class_name, "view");
                break;
            default:
                $return = parent::getCommandLink($cmd);
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
    public function getProperties() : array
    {
        return [];
    }
}
