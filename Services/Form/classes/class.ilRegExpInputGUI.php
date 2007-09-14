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
* This class represents a regular expression input property in a property form.
*
* @author Roland Küstermann <roland.kuestermann@kit.edu> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRegExpInputGUI extends ilTextInputGUI
{
	private $pattern;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("feedurl");
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng; 
		$value = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if (!$this->getRequired() && strcasecmp($value, "")== 0)
			return true;
			
		$result = preg_match ($this->pattern, $value);
		if (!$result)
			$this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
		return $result;
		
	}
	
	/**
	 * set pattern
	 * 
	 * @param string regular expression pattern
	 */
	function setPattern ($pattern)
	{
		$this->pattern = $pattern;
	}
	
	/**
	 * return pattern
	 *
	 * @return string
	 */
	function getPattern ()
	{
		return $this->pattern;
	}
	

}
