<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilObjTrainingProgrammeMembersGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjTrainingProgrammeMembersGUI: ilRepositorySearchGUI
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
	
	/**
	 * @var ilToolbarGUI
	 */
	public $toolbar;

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
		$next_class = $this->ctrl->getNextClass($this);
		
		if ($cmd == "") {
			$cmd = "view";
		}
		
		switch ($next_class) {
			case "ilrepositorysearchgui":		
				include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setCallback($this, "addUsers");				
				
				$this->ctrl->setReturn($this, "view");
				$this->ctrl->forwardCommand($rep_search);
				break;
			
			case false:
				switch ($cmd) {
					case "view":
					case "addUserFromAutoComplete":
						$cont = $this->$cmd();
						break;
					default:
						throw new ilException("ilObjTrainingProgrammeMembersGUI: ".
											  "Command not supported: $cmd");
				}
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeMembersGUI: Can't forward to next class $next_class");
		}
		
		$this->tpl->setContent($cont);
	}
	
	protected function view() {
		require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeMembersTableGUI.php");
		
		// TODO: if ($this->getTrainingProgramme()->isActive()) {
		$this->initSearchGUI();
		
		$prg_id = ilObject::_lookupObjId($this->ref_id);
		$table = new ilTrainingProgrammeMembersTableGUI($prg_id, $this);
		return $table->getHTML();
	}

	public function addUsers($a_users) {
		$prg = $this->getTrainingProgramme();
		foreach ($a_users as $user_id) {
			$prg->assignUser($user_id);
		}
	}

	protected function initSearchGUI() {
		require_once("./Services/Search/classes/class.ilRepositorySearchGUI.php");
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$this->toolbar,
			array(
				"auto_complete_name"	=> $this->lng->txt("user"),
				"submit_name"			=> $this->lng->txt("add"),
				"add_search"			=> true
			)
		);
	}
	
	protected function getTrainingProgramme() {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");
		return ilObjTrainingProgramme::getInstanceByRefId($this->ref_id);
	}
}

?>