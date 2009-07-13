<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilTextInputGUI.php");
include_once("./Services/Math/classes/class.EvalMath.php");

/**
* This class represents a formula text property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFormulaInputGUI extends ilTextInputGUI
{
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
		else
		{
			$eval = new EvalMath();
			$eval->suppress_errors = true;
			$result = $eval->e(str_replace(",", ".", ilUtil::stripSlashes($_POST[$this->getPostVar()], FALSE)));
			if ($result === false)
			{
				$this->setAlert($lng->txt("form_msg_formula_is_required"));
				return false;
			}
		}
		
		return $this->checkSubItemsInput();
	}
}
?>