<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * Date: 4/07/13
 * Time: 12:39 PM
 * To change this template use File | Settings | File Templates.
 */
include_once("./Services/Object/classes/class.ilObjectListGUI.php");
include_once("./Modules/Category/classes/class.ilObjCategoryListGUI.php");

class ilObjOrgUnitListGUI extends ilObjCategoryListGUI {
	function __construct(){
		$this->ilObjectListGUI();
		$this->enableComments(false, false);
	}

	/**
	 * Get command link url.
	 *
	 * Overwrite this method, if link target is not build by ctrl class
	 * (e.g. "forum.php"). This is the case
	 * for all links now, but bringing everything to ilCtrl should
	 * be realised in the future.
	 *
	 * @param	string		$a_cmd			command
	 *
	 * @return	string		command link url
	 */
	function getCommandLink($a_cmd)
	{
		if($this->context == self::CONTEXT_REPOSITORY || $this->context == self::CONTEXT_SHOP)
		{

			$this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $this->getCommandId());
			$cmd_link = $this->ctrl->getLinkTargetByClass("ilobjorgunitgui", $a_cmd);
			$this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $_GET["ref_id"]);
			return $cmd_link;
		}
		else
		{
			$this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", "");
			$this->ctrl->setParameterByClass($this->gui_class_name, "wsp_id", $this->ref_id);
			return $this->ctrl->getLinkTargetByClass($this->gui_class_name, $a_cmd);
		}
	}

	public function init(){
		parent::init();

		$this->type = "orgu";
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

	public function getCommands(){
		$cmds = parent::getCommands();
		$my_cmds = array();
		foreach($cmds as $cmd){
			if(!$cmd["cmd"] == "enableAdministrationPanel")
				$my_cmds[] = $cmd;
		}
		return $my_cmds;
	}
}