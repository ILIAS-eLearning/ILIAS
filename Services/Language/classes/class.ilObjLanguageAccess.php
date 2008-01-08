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
* Class ilObjLanguageAccess
*
* Languages are not under RBAC control in ILIAS
*
* This class provides access checks for language maintenance
* based on the RBAC settings of the global language folder
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilObjLanguageAccess.php $
*
* @package AccessControl
*/
class ilObjLanguageAccess
{
	/**
	* Permission check for translations
	*
	* This check is used for displaying the translation link on each page
	* The global language folder must have 'visible' and 'read' permissions
	*
	* @access   static
	* @return   boolean     translation possible (true/false)
	*/
	function _checkTranslate()
	{
		global $ilUser, $rbacsystem;

		if ($ilUser->getId())
		{
			$ref_id = ilObjLanguageAccess::_lookupLangFolderRefId();
			return $rbacsystem->checkAccess("write", (int) $ref_id);
		}
		return false;
	}


	/**
	* Permission check for language maintenance (import/export)
	*
	* The global language folder must have 'visible' and 'read' permissions
	*
	* @access   static
	* @return   boolean     maintenance possible (true/false)
	*/
	function _checkMaintenance()
	{
		global $ilUser, $rbacsystem;

		if (!$ilUser->getId())
		{
			return false;
		}
		else
		{
			$ref_id = ilObjLanguageAccess::_lookupLangFolderRefId();
			return $rbacsystem->checkAccess("read,visible", (int) $ref_id);
		}
	}


	/**
	* Lookup the ref_id of the global language folder
	*
	* @access   static
	* @return   int     	language folder ref_id
	*/
	function _lookupLangFolderRefId()
	{
		global $ilDB;
		
		$q = "SELECT ref_id FROM object_reference r, object_data d".
		" WHERE r.obj_id = d.obj_id AND d.type='lngf'";
		$set = $ilDB->query($q);
		$row = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['ref_id'];
	}
	


	/**
	* Lookup the object ID for a language key
	*
	* @access   static
	* @param    string      language key
	* @param    integer     language object id
	*/
	function _lookupId($a_key)
	{
		global $ilDB;

		$q = "SELECT obj_id FROM object_data ".
		" WHERE type = 'lng' ".
		" AND title = ".$ilDB->quote($a_key);
		$set = $ilDB->query($q);
		$row = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['obj_id'];
	}
}

?>
