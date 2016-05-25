<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a border style with all/top/right/bottom/left in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTRBLBorderStyleInputGUI extends ilFormPropertyGUI
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
		$this->setType("border_style");
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
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		foreach ($this->dirs as $dir)
		{
			$pre_value = $_POST[$this->getPostVar()][$dir]["pre_value"] = 
				ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["pre_value"]);
				
			/*
			if ($this->getRequired() && trim($num_value) == "")
			{
				$this->setAlert($lng->txt("msg_input_is_required"));
	
				return false;
			}*/
						
			$value = $pre_value;
			
			if (trim($value) != "")
			{
				switch ($dir)
				{
					case "all": $this->setAllValue($value); break;
					case "top": $this->setTopValue($value); break;
					case "bottom": $this->setBottomValue($value); break;
					case "left": $this->setLeftValue($value); break;
					case "right": $this->setRightValue($value); break;
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
			$tpl = new ilTemplate("tpl.prop_trbl_select.html", true, true, "Services/Style");
			$pre_options = array_merge(array("" => ""),
				ilObjStyleSheet::_getStyleParameterValues("border-style"));
			
			switch($dir)
			{
				case "all": $value = strtolower(trim($this->getAllValue())); break;
				case "top": $value = strtolower(trim($this->getTopValue())); break;
				case "bottom": $value = strtolower(trim($this->getBottomValue())); break;
				case "left": $value = strtolower(trim($this->getLeftValue())); break;
				case "right": $value = strtolower(trim($this->getRightValue())); break;
			}

			foreach ($pre_options as $option)
			{
				$tpl->setCurrentBlock("pre_option");
				$tpl->setVariable("VAL_PRE", $option);
				$tpl->setVariable("TXT_PRE", $option);
				if ($value == $option)
				{
					$tpl->setVariable("PRE_SELECTED", 'selected="selected"');
				}
				$tpl->parseCurrentBlock();
			}

			$tpl->setVariable("POSTVAR", $this->getPostVar());
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
		
		$this->setAllValue($a_values[$this->getPostVar()]["all"]["pre_value"]);
		$this->setBottomValue($a_values[$this->getPostVar()]["bottom"]["pre_value"]);
		$this->setTopValue($a_values[$this->getPostVar()]["top"]["pre_value"]);
		$this->setLeftValue($a_values[$this->getPostVar()]["left"]["pre_value"]);
		$this->setRightValue($a_values[$this->getPostVar()]["right"]["pre_value"]);
	}

}
