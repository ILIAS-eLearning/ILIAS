<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeExpandableProgressListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeProgressListGUI.php");

class ilStudyProgrammeExpandableProgressListGUI extends ilStudyProgrammeProgressListGUI {
	/**
	 * @var int
	 */
	protected $indent = 0;
	
	public function getIndent($a_indent) {
		return $this->indent;
	}
	
	public function setIndent($a_indent) {
		assert(is_int($a_indent));
		assert($a_indent >= 0);
		$this->indent = $a_indent;
	}
	
	protected function fillTemplate($tpl) {
		parent::fillTemplate($tpl);
		
		$tpl->setVariable("HREF_TITLE", "");
		
		$tpl->setCurrentBlock("expand");
		$tpl->setVariable("EXP_ALT", $this->il_lng->txt("expand"));
		$tpl->setVariable("EXP_IMG", $this->getExpandImageURL());
		$tpl->parseCurrentBlock();
		
		for($i = 0; $i < $this->getIndent(); $i++) {
			$tpl->touchBlock("indent");
		}
		
		$tpl->setCurrentBlock("accordion");
		$tpl->setVariable("ACCORDION_CONTENT", $this->getAccordionContentHTML());
		$tpl->parseCurrentBlock();
	}
	
	protected function getAccordionContentHTML() {
		return implode("\n", array_map(function(ilStudyProgrammeUserProgress $progress) {
			$gui = new ilStudyProgrammeExpandableProgressListGUI($progress);
			$gui->setIndent($this->getIndent() + 1);
			return $gui->getHTML();
		}, $this->progress->getChildrenProgress()));
	}
	
	protected function getExpandImageURL() {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		return ilUtil::getImagePath("tree_col.svg");
	}
	
	protected function getTitleAndIconTarget(ilStudyProgrammeUserProgress $a_progress) {
		return null;
	}
}


?>