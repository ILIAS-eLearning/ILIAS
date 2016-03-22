<?php
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevUserRoleHistoryGUI {
	
	public function __construct($user_id) {
		global $tpl;

		$this->gTpl = $tpl;
		$this->user_id = $user_id;
		$this->user_utils = gevUserUtils::getInstance($user_id);
	}

	public function render() {
		require_once("Services/GEV/Administration/classes/class.gevUserRoleHistoryTableGUI.php");
		$tbl = new gevUserRoleHistoryTableGUI($this, $this->user_id);

		$this->gTpl->setContent($tbl->getHTML());
	}
}