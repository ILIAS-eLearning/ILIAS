<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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

/**
* This class represents a date/time property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilDateTimeInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $date;
	protected $showdate = true;
	protected $time = "00:00:00";
	protected $showtime = false;
	protected $showseconds = false;
	protected $minute_step_size = 1;
	
	protected $activation_title = '';
	protected $activation_post_var = '';
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("datetime");
	}
	
	/**
	 * Enable date activation.
	 * If chosen a checkbox will be shown that gives the possibility to en/disable the date selection.
	 *
	 * @access public
	 * @param string text displayed after the checkbox
	 * @param string name of postvar
	 * @param bool checkbox checked
	 * 
	 */
	public function enableDateActivation($a_title,$a_postvar,$a_checked = true)
	{
	 	$this->activation_title = $a_title;
	 	$this->activation_post_var = $a_postvar;
	 	$this->activation_checked = $a_checked;
	}
	
	/**
	 * Get activation post var
	 *
	 * @access public
	 * 
	 */
	public function getActivationPostVar()
	{
	 	return $this->activation_post_var;
	}
	
	/**
	 * Set unix time. This function will call setDate() and set
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setUnixTime($a_time)
	{
 		if($a_time)
 		{
 			$this->setDate(date('Y-m-d',$a_time));
 			$this->setTime(date('G:i:s',$a_time));
 		}
	}
	

	/**
	* Set Date, yyyy-mm-dd.
	*
	* @param	string	$a_date	Date, yyyy-mm-dd
	*/
	function setDate($a_date)
	{
		$this->date = $a_date;
	}

	/**
	* Get Date, yyyy-mm-dd.
	*
	* @return	string	Date, yyyy-mm-dd
	*/
	function getDate()
	{
		return $this->date;
	}

	/**
	* Set Show Date Information.
	*
	* @param	boolean	$a_showdate	Show Date Information
	*/
	function setShowDate($a_showdate)
	{
		$this->showdate = $a_showdate;
	}

	/**
	* Get Show Date Information.
	*
	* @return	boolean	Show Date Information
	*/
	function getShowDate()
	{
		return $this->showdate;
	}

	/**
	* Set Time, 00:00:00.
	*
	* @param	string	$a_time	Time, 00:00:00
	*/
	function setTime($a_time)
	{
		$this->time = $a_time;
	}

	/**
	* Get Time, 00:00:00.
	*
	* @return	string	Time, 00:00:00
	*/
	function getTime()
	{
		return $this->time;
	}

	/**
	* Set Show Time Information.
	*
	* @param	boolean	$a_showtime	Show Time Information
	*/
	function setShowTime($a_showtime)
	{
		$this->showtime = $a_showtime;
	}

	/**
	* Get Show Time Information.
	*
	* @return	boolean	Show Time Information
	*/
	function getShowTime()
	{
		return $this->showtime;
	}
	
	/**
	 * Set minute step size
	 * E.g 5 => The selection will only show 00,05,10... minutes 
	 *
	 * @access public
	 * @param int minute step_size 1,5,10,15,20...
	 * 
	 */
	public function setMinuteStepSize($a_step_size)
	{
	 	$this->minute_step_size = $a_step_size;
	}
	
	/**
	 * Get minute step size
	 *
	 * @access public
	 * 
	 */
	public function getMinuteStepSize()
	{
	 	return $this->minute_step_size;
	}



	/**
	* Set Show Seconds.
	*
	* @param	boolean	$a_showseconds	Show Seconds
	*/
	function setShowSeconds($a_showseconds)
	{
		$this->showseconds = $a_showseconds;
	}

	/**
	* Get Show Seconds.
	*
	* @return	boolean	Show Seconds
	*/
	function getShowSeconds()
	{
		return $this->showseconds;
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setDate($a_values[$this->getPostVar()]["date"]);
		$this->setTime($a_values[$this->getPostVar()]["time"]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
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
		$timestamp = mktime(0,0,0,
			$_POST[$this->getPostVar()]["date"]["m"],
			$_POST[$this->getPostVar()]["date"]["d"],
			$_POST[$this->getPostVar()]["date"]["y"]);

		if ($_POST[$this->getPostVar()]["date"]["d"] != (int) date("d",$timestamp) ||
			$_POST[$this->getPostVar()]["date"]["m"] != (int) date("m",$timestamp) ||
			$_POST[$this->getPostVar()]["date"]["y"] != (int) date("Y",$timestamp))
		{
			$this->setAlert($lng->txt("exc_date_not_valid"));
			$ok = false;
		}
		
		$_POST[$this->getPostVar()]["time"] =
			str_pad($_POST[$this->getPostVar()]["time"]["h"], 2 , "0", STR_PAD_LEFT).":".
			str_pad($_POST[$this->getPostVar()]["time"]["m"], 2 , "0", STR_PAD_LEFT).":".
			str_pad($_POST[$this->getPostVar()]["time"]["s"], 2 , "0", STR_PAD_LEFT);
			
		$_POST[$this->getPostVar()]["date"] =
			str_pad($_POST[$this->getPostVar()]["date"]["y"], 4 , "0", STR_PAD_LEFT)."-".
			str_pad($_POST[$this->getPostVar()]["date"]["m"], 2 , "0", STR_PAD_LEFT)."-".
			str_pad($_POST[$this->getPostVar()]["date"]["d"], 2 , "0", STR_PAD_LEFT);

		return $ok;
	}

	/**
	* Insert property html
	*
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		
		$lng->loadLanguageModule("jscalendar");
		require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
		ilCalendarUtil::initJSCalendar();
		
		if(strlen($this->getActivationPostVar()))
		{
			$a_tpl->setCurrentBlock('prop_date_activation');
			$a_tpl->setVariable('CHECK_ENABLED_DATE',$this->getActivationPostVar());
			$a_tpl->setVariable('TXT_DATE_ENABLED',$this->activation_title);
			$a_tpl->setVariable('CHECKED_ENABLED',$this->activation_checked ? 'checked="checked"' : '');
			$a_tpl->setVariable('CHECKED_DISABLED',$this->getDisabled() ? 'disabled="disabled" ' : '');
			$a_tpl->parseCurrentBlock();
		}
		
		if ($this->getShowDate())
		{
			$a_tpl->setCurrentBlock("prop_date");
			$a_tpl->setVariable("IMG_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
			$a_tpl->setVariable("TXT_DATE_CALENDAR", $lng->txt("open_calendar"));
			$a_tpl->setVariable("DATE_ID", $this->getPostVar());
			$a_tpl->setVariable("INPUT_FIELDS_DATE", $this->getPostVar()."[date]");
			$date = explode("-", $this->getDate());
			$a_tpl->setVariable("DATE_SELECT",
				ilUtil::makeDateSelect($this->getPostVar()."[date]", $date[0], $date[1], $date[2],
					'','',array('disabled' => $this->getDisabled())));
			$a_tpl->parseCurrentBlock();
				
		}
		if ($this->getShowTime())
		{
			$a_tpl->setCurrentBlock("prop_time");
			$time = explode(":", $this->getTime());
			$a_tpl->setVariable("TIME_SELECT",
				ilUtil::makeTimeSelect($this->getPostVar()."[time]", !$this->getShowSeconds(),
				$time[0], $time[1], $time[2],
				true,array('minute_steps' => $this->getMinuteStepSize(),
							'disabled' => $this->getDisabled())));
				
			
			$a_tpl->setVariable("TXT_TIME", $this->getShowSeconds()
				? "(".$lng->txt("hh_mm_ss").")"
				: "(".$lng->txt("hh_mm").")");
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock("prop_datetime");
		if ($this->getShowTime() && $this->getShowDate())
		{
			$a_tpl->setVariable("DELIM", "<br />");
		}
		$a_tpl->parseCurrentBlock();
	}

}

