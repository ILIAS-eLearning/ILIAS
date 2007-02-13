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
* This class represents a file property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/
class ilFileInputGUI extends ilFormPropertyGUI
{
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("file");
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;

		//$_POST[$this->getPostVar()] = 
		//	ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		//if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		//{
		//	$this->setAlert($lng->txt("msg_input_is_required"));
		//
		//	return false;
		//}
		return true;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_file");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->parseCurrentBlock();
	}

}
