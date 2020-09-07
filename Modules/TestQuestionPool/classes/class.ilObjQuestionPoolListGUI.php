<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjQuestionPoolListGUI
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup ModulesTestQuestionPool
*/


include_once "Services/Object/classes/class.ilObjectListGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilObjQuestionPoolListGUI extends ilObjectListGUI
{
    protected $command_link_params = array();
    
    /**
    * constructor
    *
    */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        parent::__construct($a_context);
    }

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
        $this->type = "qpl";
        $this->gui_class_name = "ilobjquestionpoolgui";

        // general commands array
        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPoolAccess.php";
        $this->commands = ilObjQuestionPoolAccess::_getCommands();
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
            case "":
            case "questions":
                include_once "./Services/UICore/classes/class.ilFrameTargetInfo.php";
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
        global $DIC;
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $props = array();

        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
        if (!ilObjQuestionPool::_lookupOnline($this->obj_id)) {
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
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $a_cmd = explode('::', $a_cmd);

        if (count($a_cmd) == 2) {
            $cmd_link = $ilCtrl->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjQuestionPoolGUI', $a_cmd[0]), $a_cmd[1]);
        } else {
            $cmd_link = $ilCtrl->getLinkTargetByClass('ilObjQuestionPoolGUI', $a_cmd[0]);
        }

        $params = array_merge(array('ref_id' => $this->ref_id), $this->command_link_params);

        foreach ($params as $param => $value) {
            $cmd_link = ilUtil::appendUrlParameterString($cmd_link, "$param=$value", true);
        }

        return $cmd_link;
    }
} // END class.ilObjTestListGUI
