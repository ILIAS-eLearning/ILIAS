<?php
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
class ilDateTimeInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
	protected $date;
	protected $time = "00:00:00";
	protected $showtime = false;
	protected $showseconds = false;
	protected $minute_step_size = 5;
	protected $startyear = '';
	protected $invalid_input = '';

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
	* set date
	* E.g	$dt_form->setDate(new ilDateTime(time(),IL_CAL_UTC));
	* or 	$dt_form->setDate(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
	* 
	* For fullday (no timezone conversion) events use:
	* 
	* 		$dt_form->setDate(new ilDate('2008-08-01',IL_CAL_DATE));
	*		
	* @param	object	$a_date	ilDate or ilDateTime  object
	*/
	function setDate(ilDateTime $a_date = NULL)
	{
		$this->date = $a_date;
	}

	/**
	* Get Date, yyyy-mm-dd.
	*
	* @return	object	Date, yyyy-mm-dd
	*/
	function getDate()
	{
		return $this->date;
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
	* Set start year
	*
	* @param	integer	Start year
	*/
	function setStartYear($a_year)
	{
		$this->startyear = $a_year;
	}
	
	/**
	* Get start year
	*
	* @return	integer	Start year
	*/
	function getStartYear()
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
		$incoming = $a_values[$this->getPostVar()];
		
		if(is_object($incoming) && 
			$incoming instanceof ilDateTime)
		{
			$this->setDate($incoming);
		}
		else if(trim($incoming))
		{							
			$parsed = ilCalendarUtil::parseDateString($incoming, $this->getDatePickerTimeFormat());	
			if(is_object($parsed["date"]))
			{
				$this->setDate($parsed["date"]);
			}
		}
		else
		{
			$this->setDate(null);
		}

		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}
	
	protected function getDatePickerTimeFormat()
	{
		return (int)$this->getShowTime() + (int)$this->getShowSeconds();
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		if ($this->getDisabled())
		{
			return true;
		}

		$post = $_POST[$this->getPostVar()];
		
		$valid = false;		
		if(trim($post))
		{			
			$parsed = ilCalendarUtil::parseDateString($post, $this->getDatePickerTimeFormat());	
			if(is_object($parsed["date"]))
			{
				$this->setDate($parsed["date"]);
				$valid = true;
			}		
			else
			{
				$this->invalid_input = $post;
			}
		}
		else if(!$this->getRequired())
		{
			$this->setDate(null);
			$valid = true;
		}
	
		if(!$valid)
		{
			$this->setAlert($lng->txt("exc_date_not_valid"));
		}				
		return $valid;
	}
	
	/**
	 * parse properties to datepicker config
	 * 
	 * @return array
	 */
	protected function parseDatePickerConfig()
	{
		$config = null;
		if($this->getMinuteStepSize())
		{
			$config['stepping'] = (int)$this->getMinuteStepSize();
		}
		if($this->getStartYear())
		{
			$config['minDate'] = $this->getStartYear().'-01-01';
		}
		return $config;
	}

	/**
	* Insert property html
	*
	*/
	function render()
	{
		global $ilUser;
		
		$tpl = new ilTemplate("tpl.prop_datetime.html", true, true, "Services/Form");

		// config picker		
		if(!$this->getDisabled())
		{					
			
			
			$picker_id = md5($this->getPostVar()); // :TODO: unique?
			$tpl->setVariable('DATEPICKER_ID', $picker_id);				
			$tpl->setVariable('DATEPICKER_CONFIG', 
				ilCalendarUtil::addDateTimePicker(
					$picker_id, 
					$this->getDatePickerTimeFormat(),
					$this->parseDatePickerConfig())
			);
		}
		else
		{
			$tpl->setVariable('DATEPICKER_DISABLED', 'disabled="disabled" ');	
		}		
		
		// current value		
		$date_value = $this->invalid_input;			
		if(!$date_value &&
			$this->getDate())
		{			
			$out_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat(), true);		
			$date_value = $this->getDate()->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());								
		}
		$tpl->setVariable('DATEPICKER_VALUE', $date_value);	
		
		$tpl->setVariable('DATE_ID', $this->getPostVar());	
		
		return $tpl->get();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Get HTML for table filter
	*/
	function getTableFilterHTML()
	{
		$html = $this->render();
		return $html;
	}

	/**
	* serialize data
	*/
	function serializeData()
	{
		return serialize($this->getDate());
	}
	
   /**
	* unserialize data
	*/
	function unserializeData($a_data)
	{
		$data = unserialize($a_data);

		if (is_object($data))
		{
			$this->setDate($data);
		}
	}

	/**
	 * parse post value to make it comparable
	 *
	 * used by combination input gui
	 */
	function getPostValueForComparison()
	{
		// :TODO:
		return trim($_POST[$this->getPostVar()]);
	}
	
	/**
	* Get HTML for toolbar
	*/
	function getToolbarHTML()
	{
		$html = $this->render("toolbar");
		return $html;
	}
}

?>