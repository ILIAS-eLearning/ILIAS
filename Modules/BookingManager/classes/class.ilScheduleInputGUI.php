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
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		else if (strlen($this->getValidationRegexp()))
		{
			if (!preg_match($this->getValidationRegexp(), $_POST[$this->getPostVar()]))
			{
				$this->setAlert(
					$this->getValidationFailureMessage() ?
					$this->getValidationFailureMessage() :
					$lng->txt('msg_wrong_format')
				);
				return FALSE;
			}
		}
		
		return $this->checkSubItemsInput();
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
				
				if(in_array($day_value, $days_select))
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
			$tpl->setVariable("IMG_MULTI_ADD", ilUtil::getImagePath('edit_add.png'));
			$tpl->setVariable("IMG_MULTI_REMOVE", ilUtil::getImagePath('edit_remove.png'));
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
				$tpl->setVariable("ADD_STYLE", " style=\"display:none\"");				
			}
			else
			{
				$tpl->setVariable("RMV_STYLE", " style=\"display:none\"");		
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
}

?>