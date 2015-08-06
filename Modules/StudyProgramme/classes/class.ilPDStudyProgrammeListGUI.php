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
	
	/**
	 * @var ilStudyProgrammeUserAssignment[]
	 */
	protected $users_assignments;
	
	public function __construct() {
		global $lng, $ilUser;
		$this->il_lng = $lng;
		$this->il_user = $ilUser;
		
		// No need to load data, as we won't display this. 
		if (!$this->pageIsPDOverview()) {
			return;
		}
		
		$this->loadUsersAssignments();
		
		// As this won't be visible we don't have to initialize this.
		if (!$this->userHasStudyProgrammes()) {
			return;
		}
		
		
		$this->setTitle($this->il_lng->txt("objs_prg"));
	}
	
	public function getHTML() {
		// TODO: This should be determined from somewhere up in the hierarchy, as
		// this will lead to problems, when e.g. a command changes. But i don't see
		// how atm...
		if (!$this->pageIsPDOverview()) {
			return "";
		}
		
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
	
	public function fillDataSection() {
		assert($this->userHasStudyProgrammes()); // We should not get here.
		
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeAssignmentListGUI.php");
		
		$content = "";
		
		foreach ($this->users_assignments as $assignment) {
			$list_item = $this->new_ilStudyProgrammeAssignmentListGUI($assignment);
			$content .= $list_item->getHTML();
		}
		$this->tpl->setVariable("BLOCK_ROW", $content);
	}
	
	
	protected function userHasStudyProgrammes() {
		return !empty($this->users_assignments);
	}
	
	protected function pageIsPDOverview() {
		return $_GET["cmd"] == "jumpToSelectedItems";
	}
	
	protected function loadUsersAssignments() {
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
		$this->users_assignments = ilStudyProgrammeUserAssignment::getInstancesOfUser($this->il_user->getId());
	}
	
	protected function new_ilStudyProgrammeAssignmentListGUI(ilStudyProgrammeUserAssignment $a_assignment) {
		require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeAssignmentListGUI.php");
		$progress = $a_assignment->getStudyProgramme()->getProgressForAssignment($a_assignment->getId());
		return new ilStudyProgrammeProgressListGUI($progress);
	}
}