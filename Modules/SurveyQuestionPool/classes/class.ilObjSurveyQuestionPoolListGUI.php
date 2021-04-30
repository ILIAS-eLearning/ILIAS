<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjSurveyQuestionPoolListGUI
 *
 * @author Helmut Schottmueller <helmut.schottmueller@mac.com>
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjSurveyQuestionPoolListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "spl";
        $this->gui_class_name = "ilobjsurveyquestionpoolgui";

        // general commands array
        $this->commands = ilObjSurveyQuestionPoolAccess::_getCommands();
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
            case "":
            case "questions":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
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
        $ilUser = $this->user;

        $props = array();

        if (!ilObjSurveyQuestionPool::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props;
    }


    /**
    * Get command link url.
    *
    * @param	int			$a_ref_id		reference id
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        // separate method for this line
        $cmd_link = "ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=$a_cmd";

        return $cmd_link;
    }
} // END class.ilObjTestListGUI
