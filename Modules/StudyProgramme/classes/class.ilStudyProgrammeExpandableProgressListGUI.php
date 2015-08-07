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
	
	/**
	 * @var bool
	 */
	protected $js_added = false;
	
	/**
	 * @var ilTemplate
	 */
	protected $il_tpl;
	
	function __construct(ilStudyProgrammeUserProgress $a_progress) {
		parent::__construct($a_progress);
		
		global $tpl;
		$this->il_tpl = $tpl;
	}
	
	public function getIndent($a_indent) {
		return $this->indent;
	}
	
	public function setIndent($a_indent) {
		assert(is_int($a_indent));
		assert($a_indent >= 0);
		$this->indent = $a_indent;
	}
	
	public function getHTML() {
		$this->addJavaScript();
		return parent::getHTML();
	}
	
	protected function fillTemplate($tpl) {
		require_once("./Services/JSON/classes/class.ilJsonUtil.php");
		
		parent::fillTemplate($tpl);

		$tpl->setVariable("ACCORDION_ID", 'id="'.$this->getAccordionId().'"');
		
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
		$tpl->setVariable("ACCORDION_OPTIONS", ilJsonUtil::encode($this->getAccordionOptions()));
		$tpl->parseCurrentBlock();
	}
	
	protected function getAccordionContentHTML() {
		return implode("\n", array_map(function(ilStudyProgrammeUserProgress $progress) {
			$gui = new ilStudyProgrammeExpandableProgressListGUI($progress);
			$gui->setIndent($this->getIndent() + 1);
			return $gui->getHTML();
		}, $this->progress->getChildrenProgress()));
	}
	
	protected function getAccordionOptions() {
		return array
			( "orientation" => "horizontal"
			// Most propably we don't need this. Or do we want to call ilAccordion.initById?
			, "int_id" => "prg_progress_".$this->progress->getId()
			, "initial_opened" => null
			//, "save_url" => "./ilias.php?baseClass=ilaccordionpropertiesstorage&cmd=setOpenedTab&accordion_id=".$this->getId()."&user_id=".$ilUser->getId();
			, "behaviour" => "AllClosed" // or "FirstOpen"
			, "toggle_class" => 'il_PrgAccordionToggle'
			, "toggle_act_class" => 'foo'
			, "content_class" => 'il_PrgAccordionContent'
			, "width" => "auto"
			, "active_head_class" => "il_PrgAccordionHeadActive"
			, "height" => "auto"
			, "id" => $this->getAccordionId()
			, "multi" => true
			, "show_all_element" => null
			, "hide_all_element" => null
			, "reset_width" => true
			);
	}
	
	protected function getAccordionId() {
		return "prg_progress_".$this->progress->getId()."_".$this->getIndent();
	}
	
	protected function getExpandImageURL() {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		return ilUtil::getImagePath("tree_col.svg");
	}
	
	protected function getTitleAndIconTarget(ilStudyProgrammeUserProgress $a_progress) {
		return null;
	}
	
	protected function addJavaScript() {
		if ($this->js_added) {
			return false;
		}
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initConnection();
		
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQueryUI();
		$this->il_tpl->addJavaScript("./Services/Accordion/js/accordion.js", true, 3);
	}
}


?>