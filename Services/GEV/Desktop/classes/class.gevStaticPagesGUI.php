<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Static Pages GUI for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/


class gevStaticpagesGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		return $this->render();
	}

	public function render() {
		$cmd = $this->ctrl->getCmd();
		$ctpl = new ilTemplate($cmd, 0, 0, "Customizing/global/skin/genv");
		
		return $ctpl->get();
	}
}

?>