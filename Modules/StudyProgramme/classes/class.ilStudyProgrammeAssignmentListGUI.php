<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilObjStudyProgrammeListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeAssignmentListGUI {
	protected static $tpl_file = "tpl.study_programme_assignment_list_item.html";
	
	/**
	 * @var ilLanguage
	 */
	protected $il_lng;
	
	/**
	 * @var ilStudyProgrammeUserAssignment
	 */
	protected $assignment;

	/**
	 * @var string
	 */
	protected $html;

	function __construct(ilStudyProgrammeUserAssignment $a_assignment) {
		global $lng;
		$this->il_lng = $lng;
		
		$this->assignment = $a_assignment;
		$this->tpl = null;
		$this->html = null;
	}
	
	public function getHTML() {
		if ($this->html === null) {
			$prg = $this->assignment->getStudyProgramme();
			
			$tpl = $this->getTemplate("Modules/StudyProgramme", static::$tpl_file);
			$tpl->setVariable("TXT_TITLE", $prg->getTitle());
			$tpl->setVariable("TXT_DESC", $prg->getDescription());
			$tpl->setVariable("SRC_ICON", $this->getIconPath($prg->getId()));
			$tpl->setVariable("ALT_ICON", $this->getAltIcon($prg->getId()));
			$tpl->setVariable("HREF_TITLE", $this->getTargetForProgramme($prg->getId()));
			$tpl->setVariable("ICON_HREF", $this->getTargetForProgramme($prg->getId()));
			
			$this->html = $tpl->get();
		}
		return $this->html;
	}
	
	protected function getTemplate($a_component, $a_file, $a_remove_unknown_vars, $a_remove_empty_blocks) {
		return new ilTemplate($a_file, $a_remove_unknown_vars, $a_remove_empty_blocks, $a_component);
	}
	
	protected function getIconPath($a_obj_id) {
		return ilObject::_getIcon($a_obj_id, "small", "prg");
	}
	
	protected function getAltIcon($a_obj_id) {
		return $this->il_lng->txt("icon")." ".$this->il_lng->txt("obj_prg");
	}
	
	protected function getTargetForProgramme($a_obj_id) {
		return "http://www.google.de";
	}
}


?>