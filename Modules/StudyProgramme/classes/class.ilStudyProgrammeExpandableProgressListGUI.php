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
	 * @var bool
	 */
	protected $css_added = false;
	
	/**
	 * @var ilTemplate
	 */
	protected $il_tpl;
	
	/**
	 * @var ilRbacSystem
	 */
	protected $il_rbacsystem;
	
	function __construct(ilStudyProgrammeUserProgress $a_progress) {
		parent::__construct($a_progress);
		
		global $tpl, $rbacsystem;
		$this->il_tpl = $tpl;
		$this->il_rbacsystem = $rbacsystem;
	}
	
	public function getIndent() {
		return $this->indent;
	}
	
	public function setIndent($a_indent) {
		assert(is_int($a_indent));
		assert($a_indent >= 0);
		$this->indent = $a_indent;
	}
	
	public function getHTML() {
		$this->addJavaScript();
		$this->addCSS();
		return parent::getHTML();
	}
	
	protected function fillTemplate($tpl) {
		require_once("./Services/JSON/classes/class.ilJsonUtil.php");
		
		parent::fillTemplate($tpl);
		
		if ($this->showMyProgress()) {
			$tpl->setVariable("ACTIVE_HEAD", "il_PrgAccordionHeadActive");
		}

		$tpl->setVariable("ACCORDION_ID", 'id="'.$this->getAccordionId().'"');
		$tpl->setVariable("HREF_TITLE", "");
		
		$tpl->setCurrentBlock("expand");
		$tpl->setVariable("EXP_ALT", $this->il_lng->txt("expand"));
		$tpl->setVariable("EXP_IMG", $this->getExpandedImageURL());
		$tpl->setVariable("NOT_EXP_ALT", $this->il_lng->txt("expanded"));
		$tpl->setVariable("NOT_EXP_IMG", $this->getNotExpandedImageURL());
		$tpl->parseCurrentBlock();
		
		for($i = 0; $i < $this->getIndent(); $i++) {
			$tpl->touchBlock("indent");
		}
		
		$tpl->setCurrentBlock("accordion");
		if ($this->showMyProgress()) {
			$tpl->setVariable("ACCORDION_HIDE_CONTENT", "");
		}
		else {
			$tpl->setVariable("ACCORDION_HIDE_CONTENT", "ilAccHideContent");
		}
		$tpl->setVariable("ACCORDION_CONTENT", $this->getAccordionContentHTML());
		$tpl->setVariable("ACCORDION_OPTIONS", ilJsonUtil::encode($this->getAccordionOptions()));
		$tpl->parseCurrentBlock();
	}
	
	protected function getAccordionContentHTML() {
		if (!$this->progress->getStudyProgramme()->hasLPChildren()) {
			return $this->getAccordionContentProgressesHTML();
		}
		else {
			return $this->getAccordionContentCoursesHTML();
		}
	}
	
	protected function getAccordionContentProgressesHTML() {
		return implode("\n", array_map(function(ilStudyProgrammeUserProgress $progress) {
			$gui = new ilStudyProgrammeExpandableProgressListGUI($progress);
			$gui->setIndent($this->getIndent() + 1);
			return $gui->getHTML();
		}, $this->progress->getChildrenProgress()));
	}
	
	protected function getAccordionContentCoursesHTML() {
		return implode("\n", array_map(function(ilObjCourseReference $object) {
			require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeCourseListGUI.php");
			require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeContainerObjectMock.php");
			$course = ilObjectFactory::getInstanceByRefId($object->getTargetRefId());
			$item_gui = new ilStudyProgrammeCourseListGUI();
			$item_gui->enableComments(false);
			$item_gui->enableTags(false);
			$item_gui->enableIcon(true);
			$item_gui->enableDelete(false);
			$item_gui->enableCut(false);
			$item_gui->enableCopy(false);
			$item_gui->enablePayment(false);
			$item_gui->enableLink(false);
			$item_gui->enableInfoScreen(true);
			$item_gui->enableSubscribe(true);
			$item_gui->enableCheckbox(false);
			$item_gui->enableDescription(true);
			$item_gui->enableProperties(true);
			$item_gui->enablePreconditions(true);
			$item_gui->enableNoticeProperties(true);
			$item_gui->enableCommands(true, true);
			$item_gui->enableProgressInfo(true);
			$item_gui->setContainerObject(new ilStudyProgrammeContainerObjectMock($course));
			$item_gui->setIndent($this->getIndent() + 2);
			return $item_gui->getListItemHTML
				( $course->getRefId()
				, $course->getId()
				, $course->getTitle()
				, $course->getDescription()
				);
		}, $this->progress->getStudyProgramme()->getLPChildren()));
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
	
	protected function getExpandedImageURL() {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		return ilUtil::getImagePath("tree_exp.svg");
	}
	
	protected function getNotExpandedImageURL() {
		require_once("Services/Utilities/classes/class.ilUtil.php");
		return ilUtil::getImagePath("tree_col.svg");
	}
	
	protected function getTitleAndIconTarget(ilStudyProgrammeUserProgress $a_progress) {
		return null;
	}
	
	protected function showMyProgress() {
		return $_GET["prg_progress_id"] == $this->progress->getId();
	}
	
	protected function addJavaScript() {
		if ($this->js_added) {
			return false;
		}
		
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQueryUI();
		$this->il_tpl->addJavaScript("./Services/Accordion/js/accordion.js", true, 3);
		$this->js_added = true;
	}
	
	protected function addCSS() {
		if ($this->css_added) {
			return false;
		}
		
		$this->il_tpl->addCSS("Modules/StudyProgramme/templates/css/ilStudyProgramme.css");
		$this->css_added = true;
	}
}


?>