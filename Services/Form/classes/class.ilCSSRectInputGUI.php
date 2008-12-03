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
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCSSRectInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $top;
	protected $left;
	protected $right;
	protected $bottom;
	protected $size;
	protected $useUnits;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->size = 6;
		$this->useUnits = TRUE;
	}

	/**
	* Set use units.
	*
	* @param	boolean	$a_value	Use units
	*/
	function setUseUnits($a_value)
	{
		$this->useUnits = $a_value;
	}

	/**
	* Get use units
	*
	* @return	boolean use units
	*/
	function useUnits()
	{
		return $this->useUnits;
	}

	/**
	* Set Top.
	*
	* @param	string	$a_value	Top
	*/
	function setTop($a_value)
	{
		$this->top = $a_value;
	}

	/**
	* Get Top.
	*
	* @return	string	Top
	*/
	function getTop()
	{
		return $this->top;
	}

	/**
	* Set Bottom.
	*
	* @param	string	$a_value	Bottom
	*/
	function setBottom($a_value)
	{
		$this->bottom = $a_value;
	}

	/**
	* Get Bottom.
	*
	* @return	string	Bottom
	*/
	function getBottom()
	{
		return $this->bottom;
	}

	/**
	* Set Left.
	*
	* @param	string	$a_value	Left
	*/
	function setLeft($a_value)
	{
		$this->left = $a_value;
	}

	/**
	* Get Left.
	*
	* @return	string	Left
	*/
	function getLeft()
	{
		return $this->left;
	}

	/**
	* Set Right.
	*
	* @param	string	$a_value	Right
	*/
	function setRight($a_value)
	{
		$this->right = $a_value;
	}

	/**
	* Get Right.
	*
	* @return	string	Right
	*/
	function getRight()
	{
		return $this->right;
	}

	/**
	* Set Size.
	*
	* @param	int	$a_size	Size
	*/
	function setSize($a_size)
	{
		$this->size = $a_size;
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
	* Get Size.
	*
	* @return	int	Size
	*/
	function getSize()
	{
		return $this->size;
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()]["top"] = ilUtil::stripSlashes($_POST[$this->getPostVar()]["top"]);
		$_POST[$this->getPostVar()]["right"] = ilUtil::stripSlashes($_POST[$this->getPostVar()]["right"]);
		$_POST[$this->getPostVar()]["bottom"] = ilUtil::stripSlashes($_POST[$this->getPostVar()]["bottom"]);
		$_POST[$this->getPostVar()]["left"] = ilUtil::stripSlashes($_POST[$this->getPostVar()]["left"]);
		if ($this->getRequired() && ((trim($_POST[$this->getPostVar()]["top"]) == "") || (trim($_POST[$this->getPostVar()]["bottom"]) == "") || (trim($_POST[$this->getPostVar()]["left"]) == "") || (trim($_POST[$this->getPostVar()]["right"]) == "")))
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}
		if ($this->useUnits())
		{
			if ((!preg_match("/\\d+(cm|mm|in|pt|pc|px|em)/", $_POST[$this->getPostVar()]["left"])) ||
			 	(!preg_match("/\\d+(cm|mm|in|pt|pc|px|em)/", $_POST[$this->getPostVar()]["right"])) ||
				(!preg_match("/\\d+(cm|mm|in|pt|pc|px|em)/", $_POST[$this->getPostVar()]["bottom"])) ||
				(!preg_match("/\\d+(cm|mm|in|pt|pc|px|em)/", $_POST[$this->getPostVar()]["top"])))
			{
				$this->setAlert($lng->txt("msg_unit_is_required"));
				return false;
			}
		}
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		if (strlen($this->getTop()))
		{
			$a_tpl->setCurrentBlock("cssrect_value_top");
			$a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getTop()));
			$a_tpl->parseCurrentBlock();
		}
		if (strlen($this->getBottom()))
		{
			$a_tpl->setCurrentBlock("cssrect_value_bottom");
			$a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getBottom()));
			$a_tpl->parseCurrentBlock();
		}
		if (strlen($this->getLeft()))
		{
			$a_tpl->setCurrentBlock("cssrect_value_left");
			$a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getLeft()));
			$a_tpl->parseCurrentBlock();
		}
		if (strlen($this->getRight()))
		{
			$a_tpl->setCurrentBlock("cssrect_value_right");
			$a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getRight()));
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock("cssrect");
		$a_tpl->setVariable("ID", $this->getFieldId());
		$a_tpl->setVariable("SIZE", $this->getSize());
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("TEXT_TOP", $lng->txt("pos_top"));
		$a_tpl->setVariable("TEXT_RIGHT", $lng->txt("pos_right"));
		$a_tpl->setVariable("TEXT_BOTTOM", $lng->txt("pos_bottom"));
		$a_tpl->setVariable("TEXT_LEFT", $lng->txt("pos_left"));
		if ($this->getDisabled())
		{
			$a_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		$a_tpl->parseCurrentBlock();
	}
}
