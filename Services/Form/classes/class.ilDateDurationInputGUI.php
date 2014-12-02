<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Table/interfaces/interface.ilTableFilterItem.php';

/**
* input GUI for a time span (start and end date)
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesForm
*/
class ilDateDurationInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
	protected $start = null;
	protected $startyear = null;
	
	protected $start_text = '';
	protected $end_text = '';
	
	protected $minute_step_size = 5;
	protected $end = null;
	
	/**
	 * Activation full day events
	 */
	protected $activation_title = '';
	protected $activation_post_var = '';
	protected $activation_checked = true;
	
	/**
	 * Toggle datetime
	 */
	protected $toggle_fulltime = false;
	protected $toggle_fulltime_txt = '';
	protected $toggle_fulltime_checked = false;

	protected $show_empty = false;
	protected $showtime = false;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("dateduration");
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
	 * Enable toggling between date and time
	 * @param object $a_title
	 * @param object $a_checked
	 * @return 
	 */
	public function enableToggleFullTime($a_title,$a_checked)
	{
		$this->toggle_fulltime_txt = $a_title;
		$this->toggle_fulltime_checked = $a_checked;
		$this->toggle_fulltime = true;
	}
	
	/**
	 * Check if toggling between date and time enabled
	 * @return 
	 */
	public function enabledToggleFullTime()
	{
		return $this->toggle_fulltime;
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
	* Set start date
	* E.g	$dt_form->setDate(new ilDateTime(time(),IL_CAL_UTC));
	* or 	$dt_form->setDate(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
	* 
	* For fullday (no timezone conversion) events use:
	* 
	* 		$dt_form->setDate(new ilDate('2008-08-01',IL_CAL_DATE));
	*		
	* @param	object	$a_date	ilDate or ilDateTime  object
	*/
	public function setStart(ilDateTime $a_date)
	{
		$this->start = $a_date;
	}
	
	/**
	 * Set text, which will be shown before the start date
	 * @param object $a_txt
	 * @return 
	 */
	public function setStartText($a_txt)
	{
		$this->start_text = $a_txt;
	}
	
	/**
	 * get start text
	 * @return 
	 */
	public function getStartText()
	{
		return $this->start_text;
	}

	/**
	 * Set text, which will be shown before the end date
	 * @param object $a_txt
	 * @return 
	 */
	public function setEndText($a_txt)
	{
		$this->end_text = $a_txt;
	}
	
	/**
	 * Get end text
	 * @return 
	 */
	public function getEndText()
	{
		return $this->end_text;
	}

	/**
	* Get Date, yyyy-mm-dd.
	*
	* @return	object	Date, yyyy-mm-dd
	*/
	public function getStart()
	{
		return $this->start;
	}
	
	/**
	* Set end date
	* E.g	$dt_form->setDate(new ilDateTime(time(),IL_CAL_UTC));
	* or 	$dt_form->setDate(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
	* 
	* For fullday (no timezone conversion) events use:
	* 
	* 		$dt_form->setDate(new ilDate('2008-08-01',IL_CAL_DATE));
	*		
	* @param	object	$a_date	ilDate or ilDateTime  object
	*/
	public function setEnd(ilDateTime $a_date)
	{
		$this->end = $a_date;
	}

	/**
	* Get Date, yyyy-mm-dd.
	*
	* @return	object	Date, yyyy-mm-dd
	*/
	public function getEnd()
	{
		return $this->end;
	}
	
	/**
	* Set Show Empty Information.
	*
	* @param	boolean	Show Empty Information
	*/
	function setShowEmpty($a_empty)
	{
		$this->show_empty = $a_empty;
	}

	/**
	* Get Show Empty Information.
	*
	* @return	boolean	Show Empty Information
	*/
	function getShowEmpty()
	{
		return $this->show_empty;
	}
	

	/**
	* Set Show Time Information.
	*
	* @param	boolean	$a_showtime	Show Time Information
	*/
	public function setShowTime($a_showtime)
	{
		$this->showtime = $a_showtime;
	}

	/**
	* Get Show Time Information.
	*
	* @return	boolean	Show Time Information
	*/
	public function getShowTime()
	{
		return $this->showtime;
	}
	
	/**
	 * Show seconds not implemented yet
	 * @return 
	 */
	public function getShowSeconds()
	{
		return false;
	}
	
	/**
	* Set start year
	*
	* @param	integer	Start year
	*/
	public function setStartYear($a_year)
	{
		$this->startyear = $a_year;
	}
	
	/**
	* Get start year
	*
	* @return	integer	Start year
	*/
	public function getStartYear()
	{
		return $this->startyear;
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
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	public function setValueByArray($a_values)
	{
		global $ilUser;

		try {
			if(isset($a_values[$this->getPostVar()]['start']["time"]))
			{
				$this->setStart(new ilDateTime($a_values[$this->getPostVar()]['start']["date"].' '.$a_values[$this->getPostVar()]['start']["time"],
					IL_CAL_DATETIME,$ilUser->getTimeZone()));
			}
			else
			{
				if (isset($a_values[$this->getPostVar()]['start']["date"]))
				{
					$this->setStart(new ilDate($a_values[$this->getPostVar()]['start']["date"],
						IL_CAL_DATE));
				}
			}
			if(isset($a_values[$this->getPostVar()]['end']["time"]))
			{
				$this->setEnd(new ilDateTime($a_values[$this->getPostVar()]['end']["date"].' '.$a_values[$this->getPostVar()]['end']["time"],
					IL_CAL_DATETIME,$ilUser->getTimeZone()));
			}
			else
			{
				if (isset($a_values[$this->getPostVar()]['end']["date"]))
				{
					$this->setEnd(new ilDate($a_values[$this->getPostVar()]['end']["date"],
						IL_CAL_DATE));
				}
			}
			foreach($this->getSubItems() as $item)
			{
				$item->setValueByArray($a_values);
			}
		}
		catch(ilDateTimeException $e)
		{
			// Nothing
		}
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	public function checkInput()
	{
		global $lng,$ilUser;
		
		if($this->getDisabled())
		{
			return true;
		}
		
		
		$ok = true;

		// Start
		$_POST[$this->getPostVar()]['start']["date"]["y"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['start']["date"]["y"]);
		$_POST[$this->getPostVar()]['start']["date"]["m"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['start']["date"]["m"]);
		$_POST[$this->getPostVar()]['start']["date"]["d"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['start']["date"]["d"]);
		$_POST[$this->getPostVar()]['start']["time"]["h"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['start']["time"]["h"]);
		$_POST[$this->getPostVar()]['start']["time"]["m"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['start']["time"]["m"]);
		$_POST[$this->getPostVar()]['start']["time"]["s"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['start']["time"]["s"]);

		// verify date
		$dt['year'] = (int) $_POST[$this->getPostVar()]['start']['date']['y'];
		$dt['mon'] = (int) $_POST[$this->getPostVar()]['start']['date']['m'];
		$dt['mday'] = (int) $_POST[$this->getPostVar()]['start']['date']['d'];
		$dt['hours'] = (int) $_POST[$this->getPostVar()]['start']['time']['h'];
		$dt['minutes'] = (int) $_POST[$this->getPostVar()]['start']['time']['m'];
		$dt['seconds'] = (int) $_POST[$this->getPostVar()]['start']['time']['s'];


		if($this->getShowTime())
		{
			$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
		}
		else
		{
			$date = new ilDate($dt,IL_CAL_FKT_GETDATE);
		}
		if ($_POST[$this->getPostVar()]['start']["date"]["d"] != $date->get(IL_CAL_FKT_DATE,'d',$ilUser->getTimeZone()) ||
			$_POST[$this->getPostVar()]['start']["date"]["m"] != $date->get(IL_CAL_FKT_DATE,'m',$ilUser->getTimeZone()) ||
			$_POST[$this->getPostVar()]['start']["date"]["y"] != $date->get(IL_CAL_FKT_DATE,'Y',$ilUser->getTimeZone()))
		{
			// #11847
			$this->invalid_input['start'] = $_POST[$this->getPostVar()]['start']["date"];
			$this->setAlert($lng->txt("exc_date_not_valid"));
			$ok = false;
		}

		#$_POST[$this->getPostVar()]['start']['date'] = $date->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
		#$_POST[$this->getPostVar()]['start']['time'] = $date->get(IL_CAL_FKT_DATE,'H:i:s',$ilUser->getTimeZone());

		$this->setStart($date);

		// End
		$_POST[$this->getPostVar()]['end']["date"]["y"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['end']["date"]["y"]);
		$_POST[$this->getPostVar()]['end']["date"]["m"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['end']["date"]["m"]);
		$_POST[$this->getPostVar()]['end']["date"]["d"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['end']["date"]["d"]);
		$_POST[$this->getPostVar()]['end']["time"]["h"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['end']["time"]["h"]);
		$_POST[$this->getPostVar()]['end']["time"]["m"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['end']["time"]["m"]);
		$_POST[$this->getPostVar()]['end']["time"]["s"] =
			ilUtil::stripSlashes($_POST[$this->getPostVar()]['end']["time"]["s"]);

		// verify date
		$dt['year'] = (int) $_POST[$this->getPostVar()]['end']['date']['y'];
		$dt['mon'] = (int) $_POST[$this->getPostVar()]['end']['date']['m'];
		$dt['mday'] = (int) $_POST[$this->getPostVar()]['end']['date']['d'];
		$dt['hours'] = (int) $_POST[$this->getPostVar()]['end']['time']['h'];
		$dt['minutes'] = (int) $_POST[$this->getPostVar()]['end']['time']['m'];
		$dt['seconds'] = (int) $_POST[$this->getPostVar()]['end']['time']['s'];

		if($this->getShowTime())
		{
			$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
		}
		else
		{
			$date = new ilDate($dt,IL_CAL_FKT_GETDATE);
		}
		if ($_POST[$this->getPostVar()]['end']["date"]["d"] != $date->get(IL_CAL_FKT_DATE,'d',$ilUser->getTimeZone()) ||
			$_POST[$this->getPostVar()]['end']["date"]["m"] != $date->get(IL_CAL_FKT_DATE,'m',$ilUser->getTimeZone()) ||
			$_POST[$this->getPostVar()]['end']["date"]["y"] != $date->get(IL_CAL_FKT_DATE,'Y',$ilUser->getTimeZone()))
		{
			$this->invalid_input['end'] = $_POST[$this->getPostVar()]['end']["date"];
			$this->setAlert($lng->txt("exc_date_not_valid"));
			$ok = false;
		}

		#$_POST[$this->getPostVar()]['end']['date'] = $date->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
		#$_POST[$this->getPostVar()]['end']['time'] = $date->get(IL_CAL_FKT_DATE,'H:i:s',$ilUser->getTimeZone());

		$this->setEnd($date);

		return $ok;
	}
	
	/**
	* Insert property html
	*
	*/
	public function render()
	{
		global $lng,$ilUser;
		
		$tpl = new ilTemplate("tpl.prop_datetime_duration.html", true, true, "Services/Form");
		
		// Init start		
		if(is_a($this->getStart(),'ilDate'))
		{
			$start_info = $this->getStart()->get(IL_CAL_FKT_GETDATE,'','UTC'); 
		}
		elseif(is_a($this->getStart(),'ilDateTime'))
		{
			$start_info = $this->getStart()->get(IL_CAL_FKT_GETDATE,'',$ilUser->getTimeZone());
		}
		else
		{
			$this->setStart(new ilDateTime(time(), IL_CAL_UNIX));
			$start_info = $this->getStart()->get(IL_CAL_FKT_GETDATE,'',$ilUser->getTimeZone());
		}
		// display invalid input again
		if(is_array($this->invalid_input['start']))
		{
			$start_info['year'] = $this->invalid_input['start']['y'];
			$start_info['mon'] = $this->invalid_input['start']['m'];
			$start_info['mday'] = $this->invalid_input['start']['d'];		
		}
		
		// Init end
		if(is_a($this->getEnd(),'ilDate'))
		{
			$end_info = $this->getEnd()->get(IL_CAL_FKT_GETDATE,'','UTC'); 
		}
		elseif(is_a($this->getEnd(),'ilDateTime'))
		{
			$end_info = $this->getEnd()->get(IL_CAL_FKT_GETDATE,'',$ilUser->getTimeZone());
		}
		else
		{
			$this->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
			$end_info = $this->getEnd()->get(IL_CAL_FKT_GETDATE,'',$ilUser->getTimeZone());
		}
		// display invalid input again
		if(is_array($this->invalid_input['end']))
		{
			$end_info['year'] = $this->invalid_input['end']['y'];
			$end_info['mon'] = $this->invalid_input['end']['m'];
			$end_info['mday'] = $this->invalid_input['end']['d'];		
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
		
		if(strlen($this->getStartText()))
		{
			$tpl->setVariable('TXT_START',$this->getStartText());
		}
		if(strlen($this->getEndText()))
		{
			$tpl->setVariable('TXT_END',$this->getEndText());
		}
		
		// Toggle fullday
		if($this->enabledToggleFullTime())
		{
			$tpl->setCurrentBlock('toggle_fullday');
			$tpl->setVariable('FULLDAY_POSTVAR',$this->getPostVar());
			$tpl->setVariable('FULLDAY_TOGGLE_NAME',$this->getPostVar().'[fulltime]');
			$tpl->setVariable('FULLDAY_TOGGLE_CHECKED',$this->toggle_fulltime_checked ? 'checked="checked"' : '');
			$tpl->setVariable('FULLDAY_TOGGLE_DISABLED',$this->getDisabled() ? 'disabled="disabled"' : '');
			$tpl->setVariable('TXT_TOGGLE_FULLDAY',$this->toggle_fulltime_txt);
			$tpl->parseCurrentBlock();
		}
				
		$tpl->setVariable('POST_VAR',$this->getPostVar());
		include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
		$tpl->setVariable("IMG_START_CALENDAR", ilGlyphGUI::get(ilGlyphGUI::CALENDAR, $lng->txt("open_calendar")));

		$tpl->setVariable("START_ID", $this->getPostVar());
		$tpl->setVariable("DATE_ID_START", $this->getPostVar());

		$tpl->setVariable("INPUT_FIELDS_START", $this->getPostVar()."[start][date]");
		include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		$tpl->setVariable('DATE_FIRST_DAY',ilCalendarUserSettings::_getInstance()->getWeekStart());
		$tpl->setVariable("START_SELECT",
			ilUtil::makeDateSelect(
				$this->getPostVar()."[start][date]",
				$start_info['year'], $start_info['mon'], $start_info['mday'],
				$this->getStartYear(),
				true,
				array(
					'disabled' => $this->getDisabled(),
					'select_attributes' => array('onchange' => 'ilUpdateEndDate();')
					),
				$this->getShowEmpty()));

		include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
		$tpl->setVariable("IMG_END_CALENDAR", ilGlyphGUI::get(ilGlyphGUI::CALENDAR, $lng->txt("open_calendar")));

		$tpl->setVariable("END_ID", $this->getPostVar());
		$tpl->setVariable("DATE_ID_END", $this->getPostVar());
		$tpl->setVariable("INPUT_FIELDS_END", $this->getPostVar()."[end][date]");
		$tpl->setVariable("END_SELECT",
			ilUtil::makeDateSelect(
				$this->getPostVar()."[end][date]",
				$end_info['year'], $end_info['mon'], $end_info['mday'],
				$this->getStartYear(),
				true,
				array(
					'disabled' => $this->getDisabled()
					),
				$this->getShowEmpty()));
		
		if($this->getShowTime())
		{
			$tpl->setCurrentBlock("show_start_time");
			$tpl->setVariable("START_TIME_SELECT",
				ilUtil::makeTimeSelect(
					$this->getPostVar()."[start][time]",
					!$this->getShowSeconds(),
					$start_info['hours'], $start_info['minutes'], $start_info['seconds'],
					true,
					array(
						'minute_steps' => $this->getMinuteStepSize(),
						'disabled' => $this->getDisabled(),
						'select_attributes' => array('onchange' => 'ilUpdateEndDate();')
					)
				));
			
			$tpl->setVariable("TXT_START_TIME", $this->getShowSeconds()
				? "(".$lng->txt("hh_mm_ss").")"
				: "(".$lng->txt("hh_mm").")");
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("show_end_time");
			$tpl->setVariable("END_TIME_SELECT",
				ilUtil::makeTimeSelect($this->getPostVar()."[end][time]", !$this->getShowSeconds(),
				$end_info['hours'], $end_info['minutes'], $end_info['seconds'],
				true,array('minute_steps' => $this->getMinuteStepSize(),
							'disabled' => $this->getDisabled())));
			
			$tpl->setVariable("TXT_END_TIME", $this->getShowSeconds()
				? "(".$lng->txt("hh_mm_ss").")"
				: "(".$lng->txt("hh_mm").")");
			$tpl->parseCurrentBlock();
		}
		
		if ($this->getShowTime())
		{
			$tpl->setVariable("DELIM", "<br />");
		}

		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	public function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Used for table filter presentation
	 * @return string
	 */
	public function getTableFilterHTML()
	{
		return $this->render();
	}

	/**
	 * Used for storing the date duration data in session for table gui filters
	 * @return array
	 */
	public function getValue()
	{
		return array(
			'start' => $this->getStart()->get(IL_CAL_UNIX),
			'end'   => $this->getEnd()->get(IL_CAL_UNIX)
		);
	}

	/**
	 * Called from table gui with the stored session value
	 * Attention: If the user resets the table filter, a boolean false is passed by the table gui
	 * @see getValue()
	 * @param array|bool $value
	 */
	public function setValue($value)
	{
		if(is_array($value))
		{
			$this->setStart(new ilDateTime($value['start'], IL_CAL_UNIX));
			$this->setEnd(new ilDateTime($value['end'], IL_CAL_UNIX));
		}
	}
}
