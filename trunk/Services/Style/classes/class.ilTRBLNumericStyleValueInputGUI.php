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
* This class represents a numeric style property with all/top/right/bottom/left in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTRBLNumericStyleValueInputGUI extends ilFormPropertyGUI
{
	protected $value;
	protected $allowpercentage = true;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("style_numeric");
		$this->dirs = array("all", "top", "bottom", "left", "right");
	}

	/**
	* Set All Value.
	*
	* @param	string	$a_allvalue	All Value
	*/
	function setAllValue($a_allvalue)
	{
		$this->allvalue = $a_allvalue;
	}

	/**
	* Get All Value.
	*
	* @return	string	All Value
	*/
	function getAllValue()
	{
		return $this->allvalue;
	}

	/**
	* Set Top Value.
	*
	* @param	string	$a_topvalue	Top Value
	*/
	function setTopValue($a_topvalue)
	{
		$this->topvalue = $a_topvalue;
	}

	/**
	* Get Top Value.
	*
	* @return	string	Top Value
	*/
	function getTopValue()
	{
		return $this->topvalue;
	}

	/**
	* Set Bottom Value.
	*
	* @param	string	$a_bottomvalue	Bottom Value
	*/
	function setBottomValue($a_bottomvalue)
	{
		$this->bottomvalue = $a_bottomvalue;
	}

	/**
	* Get Bottom Value.
	*
	* @return	string	Bottom Value
	*/
	function getBottomValue()
	{
		return $this->bottomvalue;
	}

	/**
	* Set Left Value.
	*
	* @param	string	$a_leftvalue	Left Value
	*/
	function setLeftValue($a_leftvalue)
	{
		$this->leftvalue = $a_leftvalue;
	}

	/**
	* Get Left Value.
	*
	* @return	string	Left Value
	*/
	function getLeftValue()
	{
		return $this->leftvalue;
	}

	/**
	* Set Right Value.
	*
	* @param	string	$a_rightvalue	Right Value
	*/
	function setRightValue($a_rightvalue)
	{
		$this->rightvalue = $a_rightvalue;
	}

	/**
	* Get Right Value.
	*
	* @return	string	Right Value
	*/
	function getRightValue()
	{
		return $this->rightvalue;
	}
	
	/**
	* Set Allow Percentage.
	*
	* @param	boolean	$a_allowpercentage	Allow Percentage
	*/
	function setAllowPercentage($a_allowpercentage)
	{
		$this->allowpercentage = $a_allowpercentage;
	}

	/**
	* Get Allow Percentage.
	*
	* @return	boolean	Allow Percentage
	*/
	function getAllowPercentage()
	{
		return $this->allowpercentage;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		foreach ($this->dirs as $dir)
		{
			$num_value = $_POST[$this->getPostVar()][$dir]["num_value"] = 
				trim(ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["num_value"]));
			$num_unit = $_POST[$this->getPostVar()][$dir]["num_unit"] = 
				trim(ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["num_unit"]));
				
			/*
			if ($this->getRequired() && trim($num_value) == "")
			{
				$this->setAlert($lng->txt("msg_input_is_required"));
	
				return false;
			}*/
			
			if (!is_numeric($num_value) && $num_value != "")
			{
				$this->setAlert($lng->txt("sty_msg_input_must_be_numeric"));
				return false;
			}
			
			if (trim($num_value) != "")
			{
				switch ($dir)
				{
					case "all": $this->setAllValue($num_value.$num_unit); break;
					case "top": $this->setTopValue($num_value.$num_unit); break;
					case "bottom": $this->setBottomValue($num_value.$num_unit); break;
					case "left": $this->setLeftValue($num_value.$num_unit); break;
					case "right": $this->setRightValue($num_value.$num_unit); break;
				}
			}
		}
		
		return true;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		$layout_tpl = new ilTemplate("tpl.prop_trbl_layout.html", true, true, "Services/Style");
		
		foreach ($this->dirs as $dir)
		{
			$tpl = new ilTemplate("tpl.prop_trbl_style_numeric.html", true, true, "Services/Style");
			$unit_options = ilObjStyleSheet::_getStyleParameterNumericUnits(!$this->getAllowPercentage());
			
			switch($dir)
			{
				case "all": $value = strtolower(trim($this->getAllValue())); break;
				case "top": $value = strtolower(trim($this->getTopValue())); break;
				case "bottom": $value = strtolower(trim($this->getBottomValue())); break;
				case "left": $value = strtolower(trim($this->getLeftValue())); break;
				case "right": $value = strtolower(trim($this->getRightValue())); break;
			}
	
			$current_unit = "";
			foreach ($unit_options as $u)
			{
				if (substr($value, strlen($value) - strlen($u)) == $u)
				{
					$current_unit = $u;
				}
			}
			$disp_val = substr($value, 0, strlen($value) - strlen($current_unit));
			if ($current_unit == "")
			{
				$current_unit = "px";
			}
			
			foreach ($unit_options as $option)
			{
				$tpl->setCurrentBlock("unit_option");
				$tpl->setVariable("VAL_UNIT", $option);
				$tpl->setVariable("TXT_UNIT", $option);
				if ($current_unit == $option)
				{
					$tpl->setVariable("UNIT_SELECTED", 'selected="selected"');
				}
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setVariable("POSTVAR", $this->getPostVar());
			$tpl->setVariable("VAL_NUM", $disp_val);
			$tpl->setVariable("TXT_DIR", $lng->txt("sty_$dir"));
			$tpl->setVariable("DIR", $dir);
			
			$layout_tpl->setVariable(strtoupper($dir), $tpl->get());
		}
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $layout_tpl->get());
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		global $ilUser;
		
		$this->setAllValue($a_values[$this->getPostVar()]["all"]["num_value"].
			$a_values[$this->getPostVar()]["all"]["num_unit"]);
		$this->setBottomValue($a_values[$this->getPostVar()]["bottom"]["num_value"].
			$a_values[$this->getPostVar()]["bottom"]["num_unit"]);
		$this->setTopValue($a_values[$this->getPostVar()]["top"]["num_value"].
			$a_values[$this->getPostVar()]["top"]["num_unit"]);
		$this->setLeftValue($a_values[$this->getPostVar()]["left"]["num_value"].
			$a_values[$this->getPostVar()]["left"]["num_unit"]);
		$this->setRightValue($a_values[$this->getPostVar()]["right"]["num_value"].
			$a_values[$this->getPostVar()]["right"]["num_unit"]);
	}

}
