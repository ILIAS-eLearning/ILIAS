<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 * Date: 4/07/13
 * Time: 12:39 PM
 * To change this template use File | Settings | File Templates.
 */
include_once("./Services/Object/classes/class.ilObjectListGUI.php");

class ilObjOrgUnitListGUI extends ilObjectListGUI {

	function __construct(){
		$this->ilObjectListGUI();
		//$this->enableComments(false, false);
	}

    /**
     * initialisation
     */
    function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = false;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->payment_enabled = false;

        $this->type = "orgu";
        $this->gui_class_name = "ilobjorgunitgui";

        // general commands array
        include_once('./Modules/OrgUnit/classes/class.ilObjOrgUnitAccess.php');
        $this->commands = ilObjOrgUnitAccess::_getCommands();
    }





	/**
	 * no timing commands needed in orgunits.
	 */
	public function insertTimingsCommand(){
		return;
	}

	/**
	 * no social commands needed in orgunits.
	 */
	public function insertCommonSocialCommands(){
		return;
	}

    /**
     * insert info screen command
     */
    function insertInfoScreenCommand()
    {

        if ($this->std_cmd_only)
        {
            return;
        }
        $cmd_link = $this->getCommandLink("infoScreen");
        $cmd_frame = $this->getCommandFrame("infoScreen");
        $this->insertCommand($cmd_link, $this->lng->txt("info_short"), $cmd_frame,
            ilUtil::getImagePath("cmd_info_s.png"));
    }

    function getCommandLink($a_cmd)
    {
        $this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id",  $this->ref_id);
        //$this->ctrl->setParameterByClass("ilobjorgunitgui", "wsp_id", $this->ref_id);
        return $this->ctrl->getLinkTargetByClass("ilobjorgunitgui", $a_cmd);
    }


    /*public function getCommands(){
        $cmds = parent::getCommands();
        $my_cmds = array();
        foreach($cmds as $cmd){
            if(!$cmd["cmd"] == "enableAdministrationPanel")
                $my_cmds[] = $cmd;
        }
        return $my_cmds;
    }*/


}