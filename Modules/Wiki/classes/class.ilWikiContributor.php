<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Class ilWikiContributor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiContributor
{
	const STATUS_NOT_GRADED = 0;
	const STATUS_PASSED = 1;
	const STATUS_FAILED = 2;
	
	/**
	* Lookup current success status (STATUS_NOT_GRADED|STATUS_PASSED|STATUS_FAILED)
	*
	* @param	int		$a_obj_id	exercise id
	* @param	int		$a_user_id	member id
	* @return	mixed	false (if user is no member) or notgraded|passed|failed
	*/
	function _lookupStatus($a_obj_id, $a_user_id)
	{
		global $ilDB;

		$st = $ilDB->prepare("SELECT status FROM il_wiki_contributor ".
			"WHERE wiki_id = ? and user_id = ?",
			array("integer", "integer"));
		$set = $ilDB->execute($st, array($a_obj_id, $a_user_id));
		if($row = $ilDB->fetchAssoc($set))
		{
			return $row["status"];
		}
		return false;
	}

	/**
	* Lookup last change in mark or success status
	*
	* @param	int		$a_obj_id	exercise id
	* @param	int		$a_user_id	member id
	* @return	mixed	false (if user is no member) or notgraded|passed|failed
	*/
	function _lookupStatusTime($a_obj_id, $a_user_id)
	{
		global $ilDB;

		$st = $ilDB->prepare("SELECT status_time FROM il_wiki_contributor ".
			"WHERE wiki_id = ? and user_id = ?",
			array("integer", "integer"));
		$set = $ilDB->execute($st, array($a_obj_id, $a_user_id));
		if($row = $ilDB->fetchAssoc($set))
		{
			return $row["status_time"];
		}
		return false;
	}

	/**
	* Write success status
	*
	* @param	int		$a_obj_id		exercise id
	* @param	int		$a_user_id		member id
	* @param	int		$status			status: STATUS_NOT_GRADED|STATUS_PASSED|STATUS_FAILED
	*
	* @return	int		number of affected rows
	*/
	function _writeStatus($a_obj_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$st = $ilDB->prepareManip("REPLACE INTO il_wiki_contributor (status, wiki_id, user_id) ".
			"VALUES (?,?,?)",
			array("integer", "integer", "integer"));

		$ilDB->execute($st, array($a_status, $a_obj_id, $a_user_id));
	}

}
?>
