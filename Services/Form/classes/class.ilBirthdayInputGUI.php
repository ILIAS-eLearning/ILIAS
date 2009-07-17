<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilTextInputGUI.php");

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilBirthdayInputGUI extends ilTextInputGUI
{
	protected $dateformat;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		global $lng;
		
		parent::__construct($a_title, $a_postvar);
		$this->maxlength = 10;
		$this->size = 12;
		$this->dateformat = $lng->txt('lang_dateformat');
	}
	
	/**
	* Set the date format
	*/
	public function setDateformat($a_format)
	{
		$this->dateformat = $a_format;
	}
	
	/**
	* Get the date format
	*/
	public function getDateformat()
	{
		return $this->dateformat;
	}

	/**
	 * Return a regular expression to check a date
	 * @param  string
	 * @return string
	 */
	public function getRegexp($strFormat=false)
	{
		if (!$strFormat)
		{
			$strFormat = $this->dateformat;
		}

		if (preg_match('/[BbCcDEeFfIJKkLlMNOoPpQqRrSTtUuVvWwXxZz]+/', $strFormat))
		{
			throw new Exception(sprintf('Invalid date format "%s"', $strFormat));
		}

		$arrRegexp = array
		(
			'a' => '(?P<a>am|pm)',
			'A' => '(?P<A>AM|PM)',
			'd' => '(?P<d>0[1-9]|[12][0-9]|3[01])',
			'g' => '(?P<g>[1-9]|1[0-2])',
			'G' => '(?P<G>[0-9]|1[0-9]|2[0-3])',
			'h' => '(?P<h>0[1-9]|1[0-2])',
			'H' => '(?P<H>[01][0-9]|2[0-3])',
			'i' => '(?P<i>[0-5][0-9])',
			'j' => '(?P<j>[1-9]|[12][0-9]|3[01])',
			'm' => '(?P<m>0[1-9]|1[0-2])',
			'n' => '(?P<n>[1-9]|1[0-2])',
			's' => '(?P<s>[0-5][0-9])',
			'Y' => '(?P<Y>[0-9]{4})',
			'y' => '(?P<y>[0-9]{2})',
		);

		return preg_replace('/[a-zA-Z]/e', 'isset($arrRegexp["$0"]) ? $arrRegexp["$0"] : "$0"', $strFormat);
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
		else if (!preg_match('~'. $this->getRegexp($this->dateformat) .'~i', $_POST[$this->getPostVar()]))
		{
			$this->setAlert(sprintf($lng->txt("form_msg_wrong_date_format"), $lng->txt('lang_dateformat')));
			return false;
		}
		
		return $this->checkSubItemsInput();
	}
}
?>