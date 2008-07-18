<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* Calendar blocks, displayed in different contexts, e.g. groups and courses
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilCalendarBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilCalendarBlockGUI: ilCalendarAppointmentGUI, ilCalendarDayGUI
* @ilCtrl_Calls ilCalendarBlockGUI: ilCalendarMonthGUI, ilCalendarWeekGUI
*
* @ingroup ServicesCalendar
*/
class ilCalendarBlockGUI extends ilBlockGUI
{
	const CAL_MODE_REPOSITORY = 1;
	const CAL_MODE_PD = 2;

	static $block_type = "cal";
	static $st_data;
	
	/**
	* Constructor
	*/
	function ilCalendarBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser, $tpl;
		
		parent::ilBlockGUI();
		
		$this->setImage(ilUtil::getImagePath("icon_cals_s.gif"));
		$ilCtrl->saveParameter($this, "seed");
		
		$lng->loadLanguageModule("dateplaner");
		include_once("./Services/News/classes/class.ilNewsItem.php");
		
		// TODO: needs another switch between PD and respostory mode
		if(isset($_GET['ref_id']))
		{
			$this->initCategories(self::CAL_MODE_REPOSITORY);
			$this->setBlockId($ilCtrl->getContextObjId());
		}
		else
		{
			$this->initCategories(self::CAL_MODE_PD);
			$this->setBlockId(0);
		}

		$this->setLimit(5);			// @todo: needed?
		$this->setAvailableDetailLevels(2);
		$this->setEnableNumInfo(false);
				
		$this->setTitle($lng->txt("calendar"));
		//$this->setRowTemplate("tpl.block_calendar.html", "Services/Calendar");
		//$this->setData($data);
		$this->allow_moving = false;
		//$this->handleView();
		
		include_once('Services/Calendar/classes/class.ilDate.php');
		include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
		if ($_GET["seed"] == "")
		{
			$this->seed = new ilDate(time(),IL_CAL_UNIX);	// @todo: check this
		}
		else
		{
			$this->seed = new ilDate($_REQUEST['seed'],IL_CAL_DATE);	// @todo: check this
		}
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		
		$tpl->addCSS("./Services/Calendar/css/calendar.css");
		// @todo: this must work differently...
		$tpl->addCSS("./Services/Calendar/templates/default/delos.css");
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Is this a repository object
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		global $ilCtrl;
		
		$cmd_class = $ilCtrl->getCmdClass();
		
		if ($cmd_class == "ilcalendarappointmentgui" ||
			$cmd_class == "ilcalendardaygui" ||
			$cmd_class == "ilcalendarweekgui" ||
			$cmd_class == "ilcalendarmonthgui")
		{
			return IL_SCREEN_CENTER;
		}
		
		switch($ilCtrl->getCmd())
		{
			case "kkk":
			// return IL_SCREEN_CENTER;
			// return IL_SCREEN_FULL;
			
			default:
				//return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		// TODO: the switch between repository and personal desktop mode must be done somwhere else
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$this->categories = ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_REPOSITORY,(int)$_GET['ref_id']);
		
		
		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			case "ilcalendarappointmentgui":
				include_once("./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php");
				$app = new ilCalendarAppointmentGUI($this->seed, (int) $_GET['app_id']);
				$ilCtrl->forwardCommand($app);
				break;
				
			case "ilcalendardaygui":
				include_once('./Services/Calendar/classes/class.ilCalendarDayGUI.php');
				$day_gui = new ilCalendarDayGUI($this->seed);
				$ilCtrl->forwardCommand($day_gui);
				break;

			case "ilcalendarweekgui":
				include_once('./Services/Calendar/classes/class.ilCalendarWeekGUI.php');
				$week_gui = new ilCalendarWeekGUI($this->seed);
				$ilCtrl->forwardCommand($week_gui);
				break;

			case "ilcalendarmonthgui":
				include_once('./Services/Calendar/classes/class.ilCalendarMonthGUI.php');
				$month_gui = new ilCalendarMonthGUI($this->seed);
				$ilCtrl->forwardCommand($month_gui);
				break;

			default:
				return $this->$cmd();
		}
	}

	/**
	* Set EnableEdit.
	*
	* @param	boolean	$a_enable_edit	Edit mode on/off
	*/
	public function setEnableEdit($a_enable_edit = 0)
	{
		$this->enable_edit = $a_enable_edit;
	}

	/**
	* Get EnableEdit.
	*
	* @return	boolean	Edit mode on/off
	*/
	public function getEnableEdit()
	{
		return $this->enable_edit;
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		if ($this->getCurrentDetailLevel() > 1)
		{
			$tpl = new ilTemplate("tpl.calendar_block.html", true, true,
				"Services/Calendar");
			
			$this->addMiniMonth($tpl);
			$this->setDataSection($tpl->get());
		}
		else
		{
			$this->setDataSection($this->getOverview());
		}
	}

	/**
	* Add mini version of monthly overview
	* (Maybe extracted to another class, if used in pd calendar tab
	*/
	function addMiniMonth($a_tpl)
	{
		global $ilCtrl, $lng,$ilUser;
		
		// weekdays
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		for($i = (int) $this->user_settings->getWeekStart();$i < (7 + (int) $this->user_settings->getWeekStart());$i++)
		{
			$a_tpl->setCurrentBlock('month_header_col');
			$a_tpl->setVariable('TXT_WEEKDAY',ilCalendarUtil::_numericDayToString($i,false));
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock('month_header_col');
		$a_tpl->setVariable('TXT_WEEKDAY', "&nbsp;");
		$a_tpl->parseCurrentBlock();
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_MONTH);
		$this->scheduler->calculate();
		
		$counter = 0;
		foreach(ilCalendarUtil::_buildMonthDayList($this->seed->get(IL_CAL_FKT_DATE,'m'),
			$this->seed->get(IL_CAL_FKT_DATE,'Y'),
			$this->user_settings->getWeekStart())->get() as $date)
		{
			$counter++;
			//$this->showEvents($date);
			
			
			$a_tpl->setCurrentBlock('month_col');
			
			if(count($this->scheduler->getByDay($date,$ilUser->getTimeZone())))
			{
				$a_tpl->setVariable('DAY_CLASS','calminiapp');
				#$a_tpl->setVariable('TD_CLASS','calminiapp');
			}
			if(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
			{
				$a_tpl->setVariable('TD_CLASS','calmininow');
			}
			elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_MONTH))
			{
				$a_tpl->setVariable('TD_CLASS','calministd');
			}
			elseif(ilDateTime::_before($date,$this->seed,IL_CAL_MONTH))
			{
				$a_tpl->setVariable('TD_CLASS','calminiprev');
			}
			else
			{
				$a_tpl->setVariable('TD_CLASS','calmininext');
			}
			
			$day = $date->get(IL_CAL_FKT_DATE,'j');
			$month = $date->get(IL_CAL_FKT_DATE,'n');
			
			$month_day = $day;
			
			$ilCtrl->clearParametersByClass('ilcalendardaygui');
			$ilCtrl->setParameterByClass('ilcalendardaygui','seed',$date->get(IL_CAL_DATE));
			$a_tpl->setVariable('OPEN_DAY_VIEW', $ilCtrl->getLinkTargetByClass('ilcalendardaygui',''));
			$ilCtrl->clearParametersByClass('ilcalendardaygui');
			
			$a_tpl->setVariable('MONTH_DAY',$month_day);
			//$this->tpl->setVariable('NEW_SRC',ilUtil::getImagePath('new.gif','calendar'));
			//$this->tpl->setVariable('NEW_ALT',$this->lng->txt('cal_new_app'));
			//$this->ctrl->clearParametersByClass('ilcalendarappointmentgui');
			//$this->ctrl->setParameterByClass('ilcalendarappointmentgui','seed',$date->get(IL_CAL_DATE));
			//$this->tpl->setVariable('ADD_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','add'));
			
			//$this->tpl->setVariable('OPEN_SRC',ilUtil::getImagePath('open.gif','calendar'));
			$a_tpl->parseCurrentBlock();
			
			if($counter and !($counter % 7))
			{
				$a_tpl->setCurrentBlock('month_row');
				$ilCtrl->clearParametersByClass('ilcalendarweekgui');
				$ilCtrl->setParameterByClass('ilcalendarweekgui','seed',$date->get(IL_CAL_DATE));
				$a_tpl->setVariable('OPEN_WEEK_VIEW', $ilCtrl->getLinkTargetByClass('ilcalendarweekgui',''));
				$ilCtrl->clearParametersByClass('ilcalendarweekgui');
				$a_tpl->setVariable('TD_CLASS','calminiweek');
				$a_tpl->setVariable('WEEK',
					$date->get(IL_CAL_FKT_DATE,'W'));
				$a_tpl->parseCurrentBlock();
			}
		}
		$a_tpl->setCurrentBlock('mini_month');
		
		$a_tpl->setVariable('TXT_MONTH',
			$lng->txt('month_'.$this->seed->get(IL_CAL_FKT_DATE,'m').'_long').
				' '.$this->seed->get(IL_CAL_FKT_DATE,'Y'));
		$myseed = clone($this->seed);
		$ilCtrl->setParameterByClass('ilcalendarmonthgui','seed',$myseed->get(IL_CAL_DATE));
		$a_tpl->setVariable('OPEN_MONTH_VIEW',$ilCtrl->getLinkTargetByClass('ilcalendarmonthgui',''));
		
		$myseed->increment(ilDateTime::MONTH, -1);
		$ilCtrl->setParameterByClass("ilcolumngui",'seed',$myseed->get(IL_CAL_DATE));
		$a_tpl->setVariable('PREV_MONTH',
			$ilCtrl->getLinkTargetByClass("ilcolumngui", ""));
			
		$myseed->increment(ilDateTime::MONTH, 2);
		$ilCtrl->setParameterByClass("ilcolumngui",'seed',$myseed->get(IL_CAL_DATE));
		$a_tpl->setVariable('NEXT_MONTH',
			$ilCtrl->getLinkTargetByClass("ilcolumngui", ""));

		$ilCtrl->setParameterByClass("ilcolumngui",'seed',$_GET["seed"]);
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;
		
		
		// add edit commands
		if ($this->getEnableEdit())
		{
			$ilCtrl->setParameter($this, "add_mode", "block");
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilCalendarAppointmentGUI",
					"add"),
				$lng->txt("add"));
			$ilCtrl->setParameter($this, "add_mode", "");
		}

		if ($this->getProperty("settings") == true)
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "editSettings"),
				$lng->txt("settings"));
		}

		$ilCtrl->setParameterByClass("ilcolumngui", "seed", $_GET["seed"]);
		$ret = parent::getHTML();
		$ilCtrl->setParameterByClass("ilcolumngui", "seed", "");
		
		return $ret;
	}
	
	/**
	* get flat bookmark list for personal desktop
	*/
	function fillRow($news)
	{
		global $ilUser, $ilCtrl, $lng;

	}
	
	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
		
		include_once('./Services/Calendar/classes/class.ilCalendarSchedule.php');
		$schedule = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_INBOX);
		$events = $schedule->getChangedEvents();
		return '<div class="small">'.((int) count($events))." ".$lng->txt("cal_changed_events_header")."</div>";
	}

	function addCloseCommand($a_content_block)
	{
		global $lng, $ilCtrl;
		
		$a_content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("close"), true);
	}
	
	/**
	 * init categories
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initCategories($a_mode)
	{
		$this->mode = $a_mode;
		switch($this->mode)
		{
			case self::CAL_MODE_REPOSITORY:
				if(!is_object($this->categories))
				{
					include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
					$this->categories = ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_REPOSITORY,(int)$_GET['ref_id']);
				}
				break;
			
			case self::CAL_MODE_PD:
				if(!is_object($this->categories))
				{
					include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
					$this->categories = ilCalendarCategories::_getInstance()->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP,(int)$_GET['ref_id']);
				}
				break;
		}
	}
}

?>
