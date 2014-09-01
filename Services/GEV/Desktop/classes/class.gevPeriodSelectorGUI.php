<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* User selector (for course search gui) for managers,
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Form/classes/class.ilFormPropertyGUI.php");
require_once("Services/Form/interfaces/interface.ilMultiValuesItem.php");
require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

// i need to derive this from the input gui to override the render method,
// just to be able to use another template.
// i also ripped some stuff out that won't be needed.
class gevPeriodSelectorGUI extends ilDateDurationInputGUI {
	public function __construct(ilDate $a_start, ilDate $a_end, $a_action, $a_post_var = "period") {
		parent::__construct();
		$this->_start = $a_start;
		$this->_end = $a_end;
		$this->setPostVar($a_post_var);
		$this->setAction($a_action);
	}
	
	public function getShowDate() {
		return true;
	}
	
	public function getShowTime() {
		return false;
	}
	
	public function getMulti() {
		return false;
	}
	
	public function enabledToggleFullTime() {
		return false;
	}
	
	public function getActivationPostVar() {
		return "";
	}
	
	public function getStartText() {
		global $lng;
		return $lng->txt("gev_period");
	}
	
	public function getEndText() {
		global $lng;
		return $lng->txt("gev_until");
	}
	
	public function setAction($a_action) {
		$this->action = $a_action;
	}
	
	public function render()
	{
		global $lng,$ilUser,$tpl;
		$tpl->addJavaScript('./Services/Form/js/date_duration.js');
		
		$_tpl = new ilTemplate("tpl.gev_period_selector.html", true, true, "Services/GEV/Desktop");

		
		$_tpl->setVariable("ACTION", $this->action);
		
		// Init start		
		$start_info = $this->_start->get(IL_CAL_FKT_GETDATE,'','UTC'); 
		
		// Init end
		$end_info = $this->_end->get(IL_CAL_FKT_GETDATE,'','UTC'); 
		
		$lng->loadLanguageModule("jscalendar");
		require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
		ilCalendarUtil::initJSCalendar();
		
		$_tpl->setVariable('TXT_START',$this->getStartText());
		$_tpl->setVariable('TXT_END',$this->getEndText());
		
		$_tpl->setVariable('POST_VAR',$this->getPostVar());
		$_tpl->setVariable("IMG_START_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$_tpl->setVariable("TXT_START_CALENDAR", $lng->txt("open_calendar"));
		$_tpl->setVariable("START_ID", $this->getPostVar());
		$_tpl->setVariable("DATE_ID_START", $this->getPostVar());
			
		$_tpl->setVariable("INPUT_FIELDS_START", $this->getPostVar()."[start][date]");
		include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		$_tpl->setVariable('DATE_FIRST_DAY',ilCalendarUserSettings::_getInstance()->getWeekStart());
		$_tpl->setVariable("START_SELECT",
			ilUtil::makeDateSelect(
				$this->getPostVar()."[start][date]",
				$start_info['year'], $start_info['mon'], $start_info['mday'],
				"",
				true,
				array(
					'disabled' => $this->getDisabled(),
					'select_attributes' => array('onchange' => 'ilUpdateEndDate();')
					),
				$this->getShowEmpty()));
				
		$_tpl->setVariable("IMG_END_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$_tpl->setVariable("TXT_END_CALENDAR", $lng->txt("open_calendar"));
		$_tpl->setVariable("END_ID", $this->getPostVar());
		$_tpl->setVariable("DATE_ID_END", $this->getPostVar());
		
		$_tpl->setVariable("INPUT_FIELDS_END", $this->getPostVar()."[end][date]");
		$_tpl->setVariable("END_SELECT",
			ilUtil::makeDateSelect(
				$this->getPostVar()."[end][date]",
				$end_info['year'], $end_info['mon'], $end_info['mday'],
				"",
				true,
				array(
					'disabled' => $this->getDisabled()
					),
				$this->getShowEmpty()));
		
		$_tpl->setVariable("FILTER", $lng->txt("gev_filter"));

		return $_tpl->get();
	}
}

?>