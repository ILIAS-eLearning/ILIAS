<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Form/classes/class.ilFormPropertyGUI.php";

/**
* This class represents a text property in a property form.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* @ingroup	ModulesBookingManager
*/
class ilScheduleInputGUI extends ilFormPropertyGUI
{
	protected $value;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}

	/**
	 * Set message string for validation failure
	 * @return 
	 * @param string $a_msg
	 */
	public function setValidationFailureMessage($a_msg)
	{
		$this->validationFailureMessage = $a_msg;
	}
	
	public function getValidationFailureMessage()
	{
		return $this->validationFailureMessage;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue(self::getPostData($this->getPostVar(), false));
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$data = self::getPostData($this->getPostVar(), false);
		if(sizeof($data))
		{
			// slots may not overlap
			foreach($data as $slot => $days)
			{
				if(!$days)
				{
					$this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
					return false;
				}
				
				$parts = explode("-", $slot);
				$from = str_replace(":", "", $parts[0]);
				$to = str_replace(":", "", $parts[1]);
				
				foreach($data as $rslot => $rdays)
				{
					if($slot != $rslot && $rdays && array_intersect($days, $rdays))
					{
						$rparts = explode("-", $rslot);
						$rfrom = str_replace(":", "", $rparts[0]);
						$rto = str_replace(":", "", $rparts[1]);
						
						if(($rfrom > $from && $rfrom < $to) || 
							($rto > $from && $rto < $to) ||
							($rfrom < $from && $rto > $to))
						{
							$this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
							return false;
						}
					}					
				}				
			}

			return true;
		}
		
		if ($this->getRequired())
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}

		return true;
	}
	
	static function getPostData($a_post_var, $a_remove_invalid = true)
	{
		$res = array();		
		for($loop = 0; $loop < 24; $loop++)
		{
			$days = $_POST[$a_post_var."_days~".$loop];		
			$from = self::parseTime($_POST[$a_post_var."_from_hh~".$loop],
				$_POST[$a_post_var."_from_mm~".$loop]);
			$to = self::parseTime($_POST[$a_post_var."_to_hh~".$loop],
				$_POST[$a_post_var."_to_mm~".$loop]);
			
			// only if any part was edited (js based gui)
			if($days || $from != "00:00" || $to != "00:00")
			{				
				$slot = $from."-".$to;				
				if($days)
				{
					if(isset($res[$slot]))
					{
						$res[$slot] = array_unique(array_merge($res[$slot], $days));		
					}
					else
					{
						$res[$slot] = $days;
					}
				}
				else
				{
					$res[$slot] = array();
				}
				
				if($a_remove_invalid && !($days && $from && $to && $from != $to))
				{						
					unset($res[$slot]);
				}
			}
		}
		
		return $res;		
	}
	
	/**
	* Render item
	*/
	protected function render($a_mode = "")
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.schedule_input.html", true, true, "Modules/BookingManager");
		
		$lng->loadLanguageModule("dateplaner");
		
		$def = $this->getValue();
		if(!$def)
		{
			$def = array(null=>null);
		}
			
		$days = array("Mo", "Tu", "We", "Th", "Fr", "Sa", "Su");
		$row = 0;
		foreach($def as $slot => $days_select)
		{
			$tpl->setCurrentBlock("days");				
			foreach($days as $day)
			{
				$day_value = strtolower($day);
				
				$tpl->setVariable("ROW", $row);
				$tpl->setVariable("ID", $this->getFieldId());
				$tpl->setVariable("POST_VAR", $this->getPostVar());
				$tpl->setVariable("DAY", $day_value);
				$tpl->setVariable("TXT_DAY", $lng->txt($day."_short"));
				
				if($days_select && in_array($day_value, $days_select))
				{
					$tpl->setVariable("DAY_STATUS", " checked=\"checked\"");
				}				
				
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("row");	
			$tpl->setVariable("ROW", $row);
			$tpl->setVariable("ID", $this->getFieldId());
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("TXT_FROM", $lng->txt("cal_from"));
			$tpl->setVariable("TXT_TO", $lng->txt("cal_until"));
			include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
			$tpl->setVariable("IMG_MULTI_ADD", ilGlyphGUI::get(ilGlyphGUI::ADD));
			$tpl->setVariable("IMG_MULTI_REMOVE", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
			$tpl->setVariable("TXT_MULTI_ADD", $lng->txt("add"));
			$tpl->setVariable("TXT_MULTI_REMOVE", $lng->txt("remove"));
			
			if($slot)
			{
				$parts = explode("-", $slot);
				$from = explode(":", $parts[0]);
				$to = explode(":", $parts[1]);
				
				$tpl->setVariable("FROM_HH_VALUE", $from[0]);
				$tpl->setVariable("FROM_MM_VALUE", $from[1]);
				$tpl->setVariable("TO_HH_VALUE", $to[0]);
				$tpl->setVariable("TO_MM_VALUE", $to[1]);
			}
			
			// manage hidden buttons
			if($row > 0)
			{
				// inline needed because of JS
				 $tpl->setVariable("ADD_STYLE", " style=\"display:none\""); 
				// $tpl->setVariable("ADD_CLASS", "ilNoDisplay");				
			}
			else
			{
				// inline needed because of JS
				$tpl->setVariable("RMV_STYLE", " style=\"display:none\""); 
				// $tpl->setVariable("RMV_CLASS", "ilNoDisplay");		
			}
			
			$tpl->parseCurrentBlock();
			
			$row++;
		}
		
		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		global $tpl;		
		
		$tpl->addJavascript("Modules/BookingManager/js/ScheduleInput.js");
		
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	 * Parse/normalize incoming time values
	 * @param	string	$a_hours
	 * @param	string	$a_minutes
	 */
	protected static function parseTime($a_hours, $a_minutes)
    {
		$hours = (int)$a_hours;
		$min = (int)$a_minutes;
		if($hours > 23 || $min > 59)
		{
			return false;
		}
		return str_pad($hours, 2, "0", STR_PAD_LEFT).":".
			str_pad($min, 2, "0", STR_PAD_LEFT);
	}
}

?>