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

/** @defgroup ServicesRating Services/Rating
 */

/**
* Class ilRating
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRating
*/
class ilRating
{
	/**
	* Write rating for a user and an object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	* @param	int			$a_user_id			User ID
	* @param	int			$a_rating			Rating
	*/
	static function writeRatingForUserAndObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type,
		$a_user_id, $a_rating)
	{
		global $ilDB;
		
		$q = "REPLACE INTO il_rating (user_id, obj_id, obj_type,".
			"sub_obj_id, sub_obj_type, rating) VALUES (".
			$ilDB->quote($a_user_id).",".
			$ilDB->quote($a_obj_id).",".
			$ilDB->quote($a_obj_type).",".
			$ilDB->quote($a_sub_obj_id).",".
			$ilDB->quote($a_sub_obj_type).",".
			$ilDB->quote($a_rating).")";
		$ilDB->query($q);
	}
	
	/**
	* Get rating for a user and an object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	* @param	int			$a_user_id			User ID
	*/
	static function getRatingForUserAndObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type,
		$a_user_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_rating WHERE ".
			"user_id = ".$ilDB->quote($a_user_id)." AND ".
			"obj_id = ".$ilDB->quote($a_obj_id)." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type)." AND ".
			"sub_obj_id = ".$ilDB->quote($a_sub_obj_id)." AND ".
			"sub_obj_type = ".$ilDB->quote($a_sub_obj_type);
		$set = $ilDB->query($q);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return $rec["rating"];
	}
	
	/**
	* Get overall rating for an object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	*/
	static function getOverallRatingForObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type)
	{
		global $ilDB;
		
		$q = "SELECT count(*) as cnt, AVG(rating) as av FROM il_rating WHERE ".
			"obj_id = ".$ilDB->quote($a_obj_id)." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type)." AND ".
			"sub_obj_id = ".$ilDB->quote($a_sub_obj_id)." AND ".
			"sub_obj_type = ".$ilDB->quote($a_sub_obj_type);
		$set = $ilDB->query($q);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
		return array("cnt" => $rec["cnt"], "avg" => $rec["av"]);
	}

}

?>
