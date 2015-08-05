<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Personal Desktop-Presentation for the Study Programme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 * @ilCtrl_IsCalledBy ilPDStudyProgrammeListGUI: ilColumnGUI
 */
class ilPDStudyProgrammeListGUI extends ilBlockGUI {
	const BLOCK_TYPE = "prglist";
	
	public function __construct() {
		global $lng;
		$this->il_lng = $lng;
		
		// As this won't be visible we don't have to initialize this.
		if (!$this->userHasStudyProgrammes()) {
			return;
		}
		
		$this->setTitle($this->il_lng->txt("objs_prg"));
	}
	
	public function getHTML() {
		if (!$this->userHasStudyProgrammes()) {
			return "";
		}
		return parent::getHTML();
	}
	
	static public function getBlockType() {
		return self::BLOCK_TYPE;
	}
	
	static public function isRepositoryObject() {
		return false;
	}
	
	
	// Specific stuff
	protected function userHasStudyProgrammes() {
		return false;
	}
}