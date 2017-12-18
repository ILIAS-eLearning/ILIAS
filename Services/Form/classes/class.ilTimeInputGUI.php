<?php
// cat-tms-patch start
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");

/**
* This class represents a date/time property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTimeInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $time = "00:00:00";
	protected $minute_step_size = 5;
	protected $showseconds = false;
	protected $max_hours = 23;

	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("time");
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
	public function setShowSeconds($a_showseconds)
	{
		$this->showseconds = $a_showseconds;
	}

	/**
	* Get Show Seconds.
	*
	* @return	boolean	Show Seconds
	*/
	public function getShowSeconds()
	{
		return $this->showseconds;
	}

	/**
	 * Set the maximum of displayed hours
	 *
	 * @param int 	$max_hours
	 *
	 * @return void
	 */
	public function setMaxHours($max_hours) {
		assert('is_int($max_hours)');
		$this->max_hours = $max_hours;
	}

	/**
	 * Get the max hours
	 *
	 * @return int
	 */
	public function getMaxHours() {
		return $this->max_hours;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	public function setValueByArray($a_values)
	{
		$this->setHours($a_values[$this->getPostVar()]["hh"]);
		$this->setMinutes($a_values[$this->getPostVar()]["mm"]);
		if ($this->getShowSeconds()) {
			$this->setSeconds($a_values[$this->getPostVar()]["ss"]);
		}
	}

	/**
	* Set value by array
	*
	* @param string 	$value
	*/
	public function setValue($value)
	{
		if($this->getShowSeconds()) {
			$times = explode(":", $value);

			if(($time[0] < 0 || $times[0] > $this->getMaxHours())
				|| ($time[1] < 0 || $times[1] > 59)
				|| ($time[2] < 0 || $times[2] > 59)
			) {
				throw new Exception("Value out of bounds. Max Value is ".$this->getMaxHours().":59:59");
			}
		} else {
			$times = explode(":", $value);

			if(($time[0] < 0 || $times[0] > $this->getMaxHours())
				|| ($time[1] < 0 || $times[1] > 59)
			) {
				throw new Exception("Value out of bounds. Max Value is ".$this->getMaxHours().":59");
			}
		}

		$times = explode(":", $value);
		$this->setHours($times[0]);
		$this->setMinutes($times[1]);
		if ($this->getShowSeconds()) {
			$this->setSeconds($times[2]);
		}
	}

	/**
	* Set Hours.
	*
	* @param	int	$a_hours	Hours
	*/
	public function setHours($a_hours)
	{
		$this->hours = $a_hours;
	}

	/**
	* Get Hours.
	*
	* @return	int	Hours
	*/
	public function getHours()
	{
		return $this->hours;
	}

	/**
	* Set Minutes.
	*
	* @param	int	$a_minutes	Minutes
	*/
	public function setMinutes($a_minutes)
	{
		$this->minutes = $a_minutes;
	}

	/**
	* Get Minutes.
	*
	* @return	int	Minutes
	*/
	public function getMinutes()
	{
		return $this->minutes;
	}

	/**
	* Set Seconds.
	*
	* @param	int	$a_seconds	Seconds
	*/
	public function setSeconds($a_seconds)
	{
		$this->seconds = $a_seconds;
	}

	/**
	* Get Seconds.
	*
	* @return	int	Seconds
	*/
	public function getSeconds()
	{
		return $this->seconds;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/
	public function checkInput()
	{
		return true;
	}

	/**
	* Insert property html
	*
	*/
	public function render()
	{
		global $ilUser, $lng;
		$lng->loadLanguageModule("tms");
		$tpl = new ilTemplate("tpl.prop_time.html", true, true, "Services/Form");

		$tpl->setVariable("TXT_HOURS", $lng->txt("form_hours"));
		$val = array();
		for ($i=0; $i<=$this->getMaxHours(); $i++) {
			$val[$i] = str_pad($i, 2, "0", STR_PAD_LEFT);
		}
		$tpl->setVariable(
			"HOURS",
			ilUtil::formSelect(
				$this->getHours(),
				$this->getPostVar()."[hh]",
				$val,
				false,
				true,
				0,
				'',
				'',
				$this->getDisabled()
			)
		);

		$tpl->setVariable("TXT_MINUTES", $lng->txt("form_minutes"));
		$val = array();
		for ($i=0; $i<=59; $i) {
			$val[$i] = str_pad($i, 2, "0", STR_PAD_LEFT);
			$i = $i + $this->getMinuteStepSize();
		}
		$tpl->setVariable(
			"MINUTES",
			ilUtil::formSelect(
				$this->getMinutes(),
				$this->getPostVar()."[mm]",
				$val,
				false,
				true,
				0,
				'',
				'',
				$this->getDisabled()
			)
		);

		if ($this->getShowSeconds()) {
			$tpl->setCurrentBlock("seconds");
			$tpl->setVariable("TXT_SECONDS", $lng->txt("form_seconds"));
			$val = array();
			for ($i=0; $i<=59; $i++) {
				$val[$i] = str_pad($i, 2, "0", STR_PAD_LEFT);
			}
			$tpl->setVariable(
				"SECONDS",
				ilUtil::formSelect(
					$this->getSeconds(),
					$this->getPostVar()."[ss]",
					$val,
					false,
					true,
					0,
					'',
					'',
					$this->getDisabled()
				)
			);
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	public function insert($a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}
}
// cat-tms-patch end
