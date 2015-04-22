<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");
require_once("Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

/**
 * Class ilTrainingProgrammeIndividualPlanTableGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilTrainingProgrammeIndividualPlanTreeExplorerGUI extends ilExplorerBaseGUI {
	protected $assignment;
	
	public function __construct($a_parent_obj, ilTrainingProgrammeUserAssignment $a_ass) {
		parent::__construct("prg_tree", $a_parent_obj, null);

		global $ilCtrl, $lng, $ilDB;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->db = $ilDB;

		$this->assignment = $a_ass;
		$this->warning_img_path = ilUtil::getImagePath("icon_alert.svg");
		$this->warning_img_alt = $this->lng->txt("prg_warning_img_alt");
		$this->successful_img_path = ilUtil::getImagePath("scorm/complete.svg");
		$this->successful_img_alt = $this->lng->txt("prg_successful_img_alt");
		$this->in_progress_img_path = ilUtil::getImagePath("scorm/incomplete.svg");
		$this->in_progress_img_alt = $this->lng->txt("prg_in_progress_img_alt");
	}
	
	// Implementations of abstract methods from ilExplorerBaseGUI
	
	/**
	 * Get root node.
	 *
	 * Please note that the class does not make any requirements how
	 * nodes are represented (array or object)
	 *
	 * @return mixed root node object/array
	 */
	public function getRootNode() {
		return $this->assignment->getTrainingProgramme()->getRefId();
	}
	
	/**
	 * Get childs of node
	 *
	 * @param string $a_parent_id parent node id
	 * @return array childs
	 */
	public function getChildsOfNode($a_parent_node_id) {
		if (ilObject::_lookupType($a_parent_node_id, true) != "prg") {
			return array();
		}
	
		$prg = ilObjTrainingProgramme::getInstanceByRefId($a_parent_node_id);
		if ($prg->hasChildren()) {
			$childs = $prg->getChildren();
		}
		else {
			$childs = $prg->getLPChildren();
		}
		return array_map(function($obj) { return $obj->getRefId(); }, $childs);
	}
	
	/**
	 * Get content of a node
	 *
	 * @param mixed $a_node node array or object
	 * @return string content of the node
	 */
	public function getNodeContent($a_node) {
		$obj_id = ilObject::_lookupObjectId($a_node);
		if (ilObject::_lookupType($obj_id) == "prg") {
			$progress = ilTrainingProgrammeUserProgress::getInstanceForAssignment($obj_id, $this->assignment->getId());
			
			$tpl = new ilTemplate("tpl.individual_plan_tree_entry.html", true, true, "Modules/TrainingProgramme");
			$tpl->setVariable("PROGRESS_IMG_PATH", $this->getProgressImagePath($progress));
			$tpl->setVariable("PROGRESS_IMG_ALT", $this->getProgressImageAlt($progress));
			$tpl->setVariable("TITLE", $this->getProgressTitle($progress, $obj_id));
			$tpl->setVariable("POINTS_CURRENT", $progress->getCurrentAmountOfPoints());
			$tpl->setVariable("POINTS_REQUIRED", $progress->getAmountOfPoints());
			if (!$progress->canBeCompleted() && $progress->isRelevant()) {
				$tpl->setCurrentBlock("warning");			
				$tpl->setVariable("WARNING_IMG_PATH",$this->warning_img_path);
				$tpl->setVariable("WARNING_IMG_ALT",$this->warning_img_alt);
				$tpl->parseCurrentBlock();	
			}
			return $tpl->get();
		}
		else {
			return ilObject::_lookupTitle($obj_id);
		}
	}
	
	protected function getProgressTitle(ilTrainingProgrammeUserProgress $a_progress, $a_prg_id) {
		$title = ilObject::_lookupTitle($a_prg_id);
		if (!$a_progress->isRelevant()) {
			$title = "<del>$title</del>";
		}
		return $title;
	}
	
	protected function getProgressImagePath(ilTrainingProgrammeUserProgress $a_progress) {
		if ($a_progress->isSuccessfull()) {
			return $this->successful_img_path;
		}
		else {
			return $this->in_progress_img_path;
		}
	}

	protected function getProgressImageAlt(ilTrainingProgrammeUserProgress $a_progress) {
		if ($a_progress->isSuccessfull()) {
			return $this->successful_img_alt;
		}
		else {
			return $this->in_progress_img_alt;
		}
	}

	/**
	 * Get id of a node
	 *
	 * @param mixed $a_node node array or object
	 * @return string id of node
	 */
	public function getNodeId($a_node) {
		return $a_node;
	}
}

?>