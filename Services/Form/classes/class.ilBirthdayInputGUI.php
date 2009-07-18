<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilBirthdayInputGUI extends ilDateTimeInputGUI
{
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		if (is_array($a_values[$this->getPostVar()]["date"]))
		{
			if (checkdate($a_values[$this->getPostVar()]["date"]['m'], $a_values[$this->getPostVar()]["date"]['d'], $a_values[$this->getPostVar()]["date"]['y']))
			{
				parent::setValueByArray($a_values);
				return;
			}
		}
		else
		{
			if (!is_array($a_values[$this->getPostVar()]) && strlen($a_values[$this->getPostVar()]))
			{
				$this->setDate(new ilDate($a_values[$this->getPostVar()], IL_CAL_DATE));
			}
			else
			{
				$this->date = null;
			}
		}
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng,$ilUser;
		
		$ok = true;
		
		$_POST[$this->getPostVar()]["date"]["y"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["date"]["y"]);
		$_POST[$this->getPostVar()]["date"]["m"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["date"]["m"]);
		$_POST[$this->getPostVar()]["date"]["d"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["date"]["d"]);
		$_POST[$this->getPostVar()]["time"]["h"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["time"]["h"]);
		$_POST[$this->getPostVar()]["time"]["m"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["time"]["m"]);
		$_POST[$this->getPostVar()]["time"]["s"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["time"]["s"]);

		// verify date
		
		$dt['year'] = (int) $_POST[$this->getPostVar()]['date']['y'];
		$dt['mon'] = (int) $_POST[$this->getPostVar()]['date']['m'];
		$dt['mday'] = (int) $_POST[$this->getPostVar()]['date']['d'];
		$dt['hours'] = (int) $_POST[$this->getPostVar()]['time']['h'];
		$dt['minutes'] = (int) $_POST[$this->getPostVar()]['time']['m'];
		$dt['seconds'] = (int) $_POST[$this->getPostVar()]['time']['s'];
		if ($dt['year'] == 0 && $dt['mon'] == 0 && $dt['mday'] == 0 && $this->getRequired())
		{
			$this->date = null;
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}
		else if ($dt['year'] == 0 && $dt['mon'] == 0 && $dt['mday'] == 0)
		{
			$this->date = null;
		}
		else
		{
			if (!checkdate((int)$dt['mon'], (int)$dt['mday'], (int)$dt['year']))
			{
				$this->date = null;
				$this->setAlert($lng->txt("exc_date_not_valid"));
				return false;
			}
			$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
			$_POST[$this->getPostVar()]['date'] = $date->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
			$this->setDate($date);
		}
		return true;
	}

	/**
	* Insert property html
	*
	*/
	function render()
	{
		global $lng,$ilUser;
		
		$tpl = new ilTemplate("tpl.prop_datetime.html", true, true, "Services/Form");
		if (is_object($this->getDate()))
		{
			$date_info = $this->getDate()->get(IL_CAL_FKT_GETDATE,'','UTC'); 
		}
		else
		{
			$date_info = array(
				'year' => $_POST[$this->getPostVar()]['date']['y'],
				'mon' => $_POST[$this->getPostVar()]['date']['m'],
				'mday' => $_POST[$this->getPostVar()]['date']['d']
			);
		}
		
		$lng->loadLanguageModule("jscalendar");
		require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
		ilCalendarUtil::initJSCalendar();
		
		if(strlen($this->getActivationPostVar()))
		{
			$tpl->setCurrentBlock('prop_date_activation');
			$tpl->setVariable('CHECK_ENABLED_DATE',$this->getActivationPostVar());
			$tpl->setVariable('TXT_DATE_ENABLED',$this->activation_title);
			$tpl->setVariable('CHECKED_ENABLED',$this->activation_checked ? 'checked="checked"' : '');
			$tpl->setVariable('CHECKED_DISABLED',$this->getDisabled() ? 'disabled="disabled" ' : '');
			$tpl->parseCurrentBlock();
		}

		if ($this->getShowDate())
		{
			$tpl->setCurrentBlock("prop_date");
			$tpl->setVariable("IMG_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
			$tpl->setVariable("TXT_DATE_CALENDAR", $lng->txt("open_calendar"));
			$tpl->setVariable("DATE_ID", $this->getPostVar());
			$tpl->setVariable("INPUT_FIELDS_DATE", $this->getPostVar()."[date]");
			$tpl->setVariable("DATE_SELECT",
				ilUtil::makeDateSelect($this->getPostVar()."[date]", $date_info['year'], $date_info['mon'], $date_info['mday'],
					$this->startyear,true,array('disabled' => $this->getDisabled()), $this->getShowEmpty()));
			$tpl->parseCurrentBlock();
			
		}
		return $tpl->get();
	}
}
?>