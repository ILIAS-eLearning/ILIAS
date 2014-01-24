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
* This class represents a background image property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilBackgroundImageInputGUI extends ilFormPropertyGUI
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
		$this->setType("background_image");
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
	* Set Images.
	*
	* @param	array	$a_images	Images
	*/
	function setImages($a_images)
	{
		$this->images = $a_images;
	}

	/**
	* Get Images.
	*
	* @return	array	Images
	*/
	function getImages()
	{
		return $this->images;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$type = $_POST[$this->getPostVar()]["type"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["type"]);
		$int_value = $_POST[$this->getPostVar()]["int_value"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["int_value"]);
		$ext_value = $_POST[$this->getPostVar()]["ext_value"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["ext_value"]);
			
		if ($this->getRequired() && $type == "ext" && trim($ext_value) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}

		if ($type == "external")
		{
			$this->setValue($ext_value);
		}
		else
		{
			$this->setValue($int_value);
		}
		
		return true;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		$tpl = new ilTemplate("tpl.prop_background_image.html", true, true, "Services/Style");

		$tpl->setVariable("POSTVAR", $this->getPostVar());
		
		$int_options = array_merge(array("" => ""), $this->getImages());
		
		$value = trim($this->getValue());

		if (is_int(strpos($value, "/")))
		{
			$current_type = "ext";
			$tpl->setVariable("EXTERNAL_SELECTED", 'checked="checked"');
			$tpl->setVariable("VAL_EXT", ilUtil::prepareFormOutput($value));
		}
		else
		{
			$current_type = "int";
			$tpl->setVariable("INTERNAL_SELECTED", 'checked="checked"');
		}
		
		foreach ($int_options as $option)
		{
			$tpl->setCurrentBlock("int_option");
			$tpl->setVariable("VAL_INT", $option);
			$tpl->setVariable("TXT_INT", $option);

			if ($current_type == "int" && $value == $option)
			{
				$tpl->setVariable("INT_SELECTED", 'selected="selected"');
			}
			$tpl->parseCurrentBlock();
		}

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
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
		
		if ($a_values[$this->getPostVar()]["type"] == "internal")
		{
			$this->setValue($a_values[$this->getPostVar()]["int_value"]);
		}
		else
		{
			$this->setValue($a_values[$this->getPostVar()]["ext_value"]);
		}
	}
}
