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


/**
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjCourseGUI.php,v 1.4 2003/07/07 17:46:57 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjCourseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "crs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)
		//$roles = $newObj->initDefaultRoles();

		// put here your object specific stuff	

		// always send a message
		sendInfo($this->lng->txt("crs_added"),true);
		
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}
} // END class.ilObjCourseGUI
?>
