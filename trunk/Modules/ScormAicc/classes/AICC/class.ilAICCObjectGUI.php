<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Modules/ScormAicc/classes/AICC/class.ilAICCObject.php");
include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCCourseGUI.php");
include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnitGUI.php");
include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCBlockGUI.php");
		
/**
* Parent object for AICC GUI objects
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilAICCObjectGUI
{
	var $sc_object;
	var $tpl;
	var $lng;


	function ilAICCObjectGUI($a_id = 0)
	{
		global $tpl, $lng;

		if($a_id != 0)
		{
			$this->sc_object =& new ilAICCUnit($a_id);
		}
		$this->tpl =& $tpl;
		$this->lng =& $lng;
	}

	/**
	* get instance of specialized GUI class
	*
	* static
	*/
	function &getInstance($a_id)
	{
		$object = new ilAICCObject($a_id);
		switch($object->getType())
		{
			case "sbl":					// Block
				$block =& new ilAICCBlockGUI($a_id);
				return $block;
				break;

			case "sau":					// assignable unit
				$sau =& new ilAICCUnitGUI($a_id);
				return $sau;
				break;
				
			case "shd":					// course
				$shd =& new ilAICCCourseGUI($a_id);
				return $shd;
				break;
		}
	}


	function displayParameter($a_name, $a_value)
	{
		$this->tpl->setCurrentBlock("parameter");
		$this->tpl->setVariable("TXT_PARAMETER_NAME", $a_name);
		$this->tpl->setVariable("TXT_PARAMETER_VALUE", $a_value);
		$this->tpl->parseCurrentBlock();
	}
}
?>
