<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjTrainingProgrammeMembersGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilObjTrainingProgrammeMembersGUI {
	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;
	
	/**
	 * @var ilObjTrainingProgramme
	 */
	public $object;
	
	/**
	 * @var ilLog
	 */
	protected $ilLog;
	
	/**
	 * @var Ilias
	 */
	public $ilias;

	/**
	 * @var ilLng
	 */
	public $lng;

	protected $parent_gui;

	public function __construct($a_parent_gui, $a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;

		$this->ref_id = $a_ref_id;
		$this->parent_gui = $a_parent_gui;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;
		$this->lng = $lng;
		
		$this->object = null;

		$lng->loadLanguageModule("prg");
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		if ($cmd == "") {
			$cmd = "view";
		}
		
		switch ($cmd) {
			case "view":
				$cont = $this->$cmd();
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeMembersGUI: ".
									  "Command not supported: $cmd");
		}
		
		$this->tpl->setContent($cont);
	}
	
	protected function view() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeMembersTableGUI.php");
		
		$table = new ilTrainingProgrammeMembersTableGUI($this);
		return $table->getHTML();
	}
}

?>