<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeProgressListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeProgressListGUI {
	protected static $tpl_file = "tpl.progress_list_item.html";
	
	/**
	 * @var ilLanguage
	 */
	protected $il_lng;
	
	/**
	 * @var ilCtrl
	 */
	protected $il_ctrl;
	
	/**
	 * @var ilStudyProgrammeUserProgress
	 */
	protected $progress;

	/**
	 * @var string
	 */
	protected $html;

	function __construct(ilStudyProgrammeUserProgress $a_progress) {
		global $lng, $ilCtrl;
		$this->il_lng = $lng;
		$this->il_lng->loadLanguageModule("prg");
		$this->il_ctrl = $ilCtrl;
		
		$this->progress = $a_progress;
		$this->tpl = null;
		$this->html = null;
	}
	
	public function getHTML() {
		if ($this->html === null) {
			$tpl = $this->getTemplate("Modules/StudyProgramme", static::$tpl_file, true, true);
			$this->fillTemplate($tpl);
			$this->html = $tpl->get();
		}
		return $this->html;
	}
	
	protected function fillTemplate($tpl) {
		$programme = $this->progress->getStudyProgramme();
		
		$title_and_icon_target = $this->getTitleAndIconTarget($this->progress);
		
		if ($title_and_icon_target) {
			$tpl->setCurrentBlock("linked_icon");
			$tpl->setVariable("SRC_ICON", $this->getIconPath($programme->getId()));
			$tpl->setVariable("ALT_ICON", $this->getAltIcon($programme->getId()));
			$tpl->setVariable("ICON_HREF", $title_and_icon_target);
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("linked_title");
			$tpl->setVariable("TXT_TITLE", $programme->getTitle());
			$tpl->setVariable("HREF_TITLE", $title_and_icon_target);
			$tpl->parseCurrentBlock();
		}
		else {
			$tpl->setCurrentBlock("not_linked_icon");
			$tpl->setVariable("SRC_ICON", $this->getIconPath($programme->getId()));
			$tpl->setVariable("ALT_ICON", $this->getAltIcon($programme->getId()));
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("not_linked_title");
			$tpl->setVariable("TXT_TITLE", $programme->getTitle());
			$tpl->parseCurrentBlock();
		}
		
		
		$tpl->setVariable("TXT_DESC", $programme->getDescription());
		$tpl->setVariable("PROGRESS_BAR", $this->buildProgressBar($this->progress));
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
	
	protected function getTitleAndIconTarget(ilStudyProgrammeUserProgress $a_progress) {
		$this->il_ctrl->setParameterByClass("ilPersonalDesktopGUI", "prg_progress_id", $a_progress->getId());
		$link = $this->il_ctrl->getLinkTargetByClass("ilPersonalDesktopGUI", "jumpToStudyProgramme");
		$this->il_ctrl->setParameterByClass("ilPersonalDesktopGUI", "prg_progress_id", null);
		return $link;
	}
	
	protected function buildProgressBar(ilStudyProgrammeUserProgress $a_progress) {
		$tooltip_id = "prg_".$a_progress->getId();
		
		$required_amount_of_points = $a_progress->getAmountOfPoints();
		$maximum_possible_amount_of_points = $a_progress->getMaximumPossibleAmountOfPoints();
		$current_amount_of_points = $a_progress->getCurrentAmountOfPoints();
		
		if ($maximum_possible_amount_of_points > 0) {
			$current_percent = (int)($current_amount_of_points * 100 / $maximum_possible_amount_of_points);
			$required_percent = (int)($required_amount_of_points * 100 / $maximum_possible_amount_of_points);
		}
		else {
			if ($a_progress->isSuccessfull()) {
				$current_percent = 100;
				$required_percent = 100;
			}
			else {
				$current_percent = 0;
				$required_percent = 100;
			}
		}
		
		$tt_txt = $this->buildToolTip($a_progress);
		$progress_status = $this->buildProgressStatus($a_progress);
		
		return $this->buildProgressBarRaw($tooltip_id, $tt_txt, $current_percent, $required_percent, $progress_status);
	}
	
	protected function buildToolTip(ilStudyProgrammeUserProgress $a_progress) {
		return sprintf( $this->il_lng->txt("prg_progress_info")
					  , $a_progress->getCurrentAmountOfPoints()
					  , $a_progress->getAmountOfPoints()
					  );
	}
	
	protected function buildProgressStatus(ilStudyProgrammeUserProgress $a_progress) {
		return sprintf( $this->il_lng->txt("prg_progress_status")
					  , $a_progress->getCurrentAmountOfPoints()
					  , $a_progress->getAmountOfPoints()
					  );
	}

	protected function buildProgressBarRaw($a_tooltip_id, $a_tt_txt, $a_result_in_percent, $a_limit_in_percent, $a_progress_status = null) {
		assert(is_string($a_tooltip_id));
		assert(is_string($a_tt_txt));
		assert(is_int($a_result_in_percent));
		assert($a_result_in_percent >= 0);
		assert($a_result_in_percent <= 100);
		assert(is_int($a_limit_in_percent));
		assert($a_limit_in_percent >= 0);
		assert($a_limit_in_percent <= 100);
		assert($a_progress_status === null || is_string($a_progress_status));
		
		// Shameless copy of ilContainerObjectiveGUI::buildObjectiveProgressBar with modifications.
		// I wish i could just use it, but there are some crs specific things that aren't parametrized...
		$tpl = new ilTemplate("tpl.objective_progressbar.html", true, true, "Services/Container");
		
		if($a_result_in_percent >= $a_limit_in_percent) {
			$bar_color = "#80f080";
		}
		else {
			$bar_color = "#f08080";
		}
		
		$limit_pos = (121-ceil(125/100*$a_limit_in_percent))*-1;
		
		$tpl->setCurrentBlock("statusbar_bl");
		$tpl->setVariable("PERC_STATUS", $a_result_in_percent);
		$tpl->setVariable("LIMIT_POS", $limit_pos);
		$tpl->setVariable("PERC_WIDTH", $a_result_in_percent);
		$tpl->setVariable("PERC_COLOR", $bar_color);
		$tpl->setVariable("BG_COLOR", "#fff");
		$tpl->setVariable("TT_ID", $a_tooltip_id);
		$tpl->parseCurrentBlock();
		
		if ($a_progress_status) {
			$tpl->setCurrentBlock("statustxt_bl");
			$tpl->setVariable("TXT_PROGRESS_STATUS", $a_progress_status);
			$tpl->parseCurrentBlock();
		}
		
		include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
		ilTooltipGUI::addTooltip($a_tooltip_id, $a_tt_txt);
		
		return $tpl->get();
	}
}


?>