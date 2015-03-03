<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectListGUI.php");

/**
 * Class ilObjTrainingProgrammeListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilObjTrainingProgrammeListGUI extends ilObjectListGUI {

	/**
	 * @var ilTemplate
	 */
	protected $tpl;


	function __construct() {
		global $tpl;
		$this->ilObjectListGUI();
		$this->tpl = $tpl;
		//$this->enableComments(false, false);
	}


	/**
	 * initialisation
	 */
	function init() {
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->info_screen_enabled = true;
		$this->copy_enabled = false;
		$this->subscribe_enabled = false;
		$this->link_enabled = false;
		$this->payment_enabled = false;

		$this->type = "prg";
		$this->gui_class_name = "ilobjtrainingprogrammegui";

		// general commands array
		include_once('./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeAccess.php');
		$this->commands = ilObjTrainingProgrammeAccess::_getCommands();
	}


	/**
	 * no timing commands needed for program.
	 */
	public function insertTimingsCommand() {
		return;
	}


	/**
	 * no social commands needed in program.
	 */
	public function insertCommonSocialCommands() {
		return;
	}


	/**
	 * insert info screen program
	 */
	/*function insertInfoScreenCommand() {

		if ($this->std_cmd_only) {
			return;
		}
		$cmd_link = $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
		$cmd_frame = $this->getCommandFrame("infoScreen");

		$this->insertCommand($cmd_link, $this->lng->txt("info_short"), $cmd_frame, ilUtil::getImagePath("icon_info.svg"));
	}*/


	/**
	 * @param string $a_cmd
	 *
	 * @return string
	 */
	public function getCommandLink($a_cmd) {
		$this->ctrl->setParameterByClass("ilobjtrainingprogrammegui", "ref_id", $this->ref_id);

		return $this->ctrl->getLinkTargetByClass("ilobjtrainingprogrammegui", $a_cmd);
	}
}


?>