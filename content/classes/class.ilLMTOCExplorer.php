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

/*
* Explorer View for Learning Module Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

require_once("content/classes/class.ilLMExplorer.php");

class ilLMTOCExplorer extends ilLMExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilLMTOCExplorer($a_target,&$a_lm_obj)
	{
		parent::ilLMExplorer($a_target, $a_lm_obj);
	}

		/**
	* Creates Get Parameter
	* @access	private
	* @param	string
	* @param	integer
	* @return	string
	*/
	function createTarget($a_type,$a_child)
	{
		// SET expand parameter:
		//     positive if object is expanded
		//     negative if object is compressed
		$a_child = $a_type == '+' ? $a_child : -(int) $a_child;

		return $_SERVER["SCRIPT_NAME"]."?frame=".$_GET["frame"].
			"&cmd=".$_GET["cmd"]."&ref_id=".$this->lm_obj->getRefId()."&mexpand=".$a_child;
	}



} // END class.ilLMEditorExplorer
?>
