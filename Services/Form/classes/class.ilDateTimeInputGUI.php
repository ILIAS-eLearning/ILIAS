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
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjUser
	 */
	protected $user;

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
		global $DIC;

		$this->lng = $DIC->language();
		$this->user = $DIC->user();
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
		$this->setDate(ilCalendarUtil::parseIncomingDate($incoming, $this->getDatePickerTimeFormat()));
				
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}
	
	protected function getDatePickerTimeFormat()
	{
		return (int)$this->getShowTime() + (int)$this->getShowSeconds();
	}
	
	public function hasInvalidInput()
	{
		return (bool)$this->invalid_input;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		$lng = $this->lng;
		
		if ($this->getDisabled())
		{
			return true;
		}

		$post = $_POST[$this->getPostVar()];
		
		// always done to make sure there are no obsolete values left
		$this->setDate(null);
		
		$valid = false;		
		if(trim($post))
		{			
			$parsed = ilCalendarUtil::parseIncomingDate($post, $this->getDatePickerTimeFormat());
			if($parsed)
			{
				$this->setDate($parsed);											
				$valid = true;
			}					
		}
		else if(!$this->getRequired())
		{			
			$valid = true;
		}				
		
		if($valid && 
			$this->getDate() &&
			$this->getStartYear() &&
			$this->getDate()->get(IL_CAL_FKT_DATE, "Y") < $this->getStartYear())
		{
			$valid = false;
		}
		
		if(!$valid)
		{
			$this->invalid_input = $post;	
			$_POST[$this->getPostVar()] = null;
			
			$this->setAlert($lng->txt("form_msg_wrong_date"));
		}
		else
		{
			if($this->getDate() !== null)
			{
				// getInput() should return a generic format
				$post_format = $this->getShowTime()
					? IL_CAL_DATETIME
					: IL_CAL_DATE;
				$_POST[$this->getPostVar()] = $this->getDate()->get($post_format);
			}
			else
			{
				$_POST[$this->getPostVar()] = null;
			}
		}
		
		if($valid)
		{
			$valid = $this->checkSubItemsInput();
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
		$ilUser = $this->user;
		
		$tpl = new ilTemplate("tpl.prop_datetime.html", true, true, "Services/Form");

		// config picker		
		if(!$this->getDisabled())
		{											
			$picker_id = md5($this->getPostVar()); // :TODO: unique?
			$tpl->setVariable('DATEPICKER_ID', $picker_id);				
			
			ilCalendarUtil::addDateTimePicker(
				$picker_id, 
				$this->getDatePickerTimeFormat(),
				$this->parseDatePickerConfig(),
				null,
				null,
				null,
				"subform_".$this->getPostVar()
			);
		}
		else
		{
			$tpl->setVariable('DATEPICKER_DISABLED', 'disabled="disabled" ');	
		}		
		
		// :TODO: i18n?
		$pl_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat());	
		$tpl->setVariable('PLACEHOLDER', $pl_format);		
		
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
		
		if($this->getRequired())
		{
			$tpl->setVariable("REQUIRED", "required=\"required\"");
		}		
		
		return $tpl->get();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert($a_tpl)
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
		if($this->getDate())
		{
			return serialize($this->getDate()->get(IL_CAL_UNIX));
		}
	}
	
   /**
	* unserialize data
	*/
	function unserializeData($a_data)
	{
		$tmp = unserialize($a_data);
		if($tmp)
		{		
			// we used to serialize the complete instance
			if(is_object($tmp))
			{
				$date = $tmp;
			}
			else
			{
				$date = $this->getShowTime()
					? new ilDateTime($tmp, IL_CAL_UNIX)
					: new ilDate($tmp, IL_CAL_UNIX);		
			}
			$this->setDate($date);
		}
		else
		{
			$this->setDate(null);
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
	
	public function hideSubForm()
	{
		return (!$this->getDate() || $this->getDate()->isNull());
	}
}

?>