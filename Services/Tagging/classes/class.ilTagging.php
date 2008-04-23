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

/** @defgroup ServicesTagging Services/Tagging
 */

/**
* Class ilTagging
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesTagging
*/
class ilTagging
{
	/**
	* Write tags for a user and an object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	* @param	int			$a_user_id			User ID
	* @param	array		$a_tags				Tags
	*/
	static function writeTagsForUserAndObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type,
		$a_user_id, $a_tags)
	{
		global $ilDB;
		
		$q = "DELETE FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id)." AND ".
			"obj_id = ".$ilDB->quote($a_obj_id)." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type)." AND ".
			"sub_obj_id = ".$ilDB->quote($a_sub_obj_id)." AND ".
			"sub_obj_type = ".$ilDB->quote($a_sub_obj_type);
		$ilDB->query($q);
		
		if (is_array($a_tags))
		{
			$inserted = array();
			foreach($a_tags as $tag)
			{
				if (!in_array($tag, $inserted))
				{
					$tag = str_replace(" ", "_", trim($tag));
					$q = "INSERT INTO il_tag (user_id, obj_id, obj_type,".
						"sub_obj_id, sub_obj_type, tag) VALUES (".
						$ilDB->quote($a_user_id).",".
						$ilDB->quote($a_obj_id).",".
						$ilDB->quote($a_obj_type).",".
						$ilDB->quote($a_sub_obj_id).",".
						$ilDB->quote($a_sub_obj_type).",".
						$ilDB->quote($tag).")";
					$ilDB->query($q);

					$inserted[] = $tag;
				}
			}
		}
	}
	
	/**
	* Get tags for a user and an object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	* @param	int			$a_user_id			User ID
	*/
	static function getTagsForUserAndObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type,
		$a_user_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id)." AND ".
			"obj_id = ".$ilDB->quote($a_obj_id)." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type)." AND ".
			"sub_obj_id = ".$ilDB->quote($a_sub_obj_id)." AND ".
			"sub_obj_type = ".$ilDB->quote($a_sub_obj_type).
			" ORDER BY tag ASC";
		$set = $ilDB->query($q);
		$tags = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tags[] = $rec["tag"];
		}
		
		return $tags;
	}
	
	/**
	* Get tags for an object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	*/
	static function getTagsForObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type)
	{
		global $ilDB;
		
		$q = "SELECT count(user_id) as cnt, tag FROM il_tag WHERE ".
			"obj_id = ".$ilDB->quote($a_obj_id)." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type)." AND ".
			"sub_obj_id = ".$ilDB->quote($a_sub_obj_id)." AND ".
			"sub_obj_type = ".$ilDB->quote($a_sub_obj_type).
			"GROUP BY tag ORDER BY tag ASC";
		$set = $ilDB->query($q);
		$tags = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tags[] = $rec;
		}

		return $tags;
	}

	/**
	* Get tags for a user.
	*
	* @param	int			$a_user_id			User ID
	*/
	static function getTagsForUser($a_user_id)
	{
		global $ilDB;
		
		$q = "SELECT count(*) as cnt, tag FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id).
			" GROUP BY tag ORDER BY tag ASC";
		$set = $ilDB->query($q);
		$tags = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tags[] = $rec;
		}

		return $tags;
	}

	/**
	* Get objects for tag and user
	*
	* @param	int			$a_user_id			User ID
	*/
	static function getObjectsForTagAndUser($a_user_id, $a_tag)
	{
		global $ilDB;
		
		$q = "SELECT * FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id).
			" AND tag = ".$ilDB->quote($a_tag);

		$set = $ilDB->query($q);
		$objects = array();
		while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$objects[] = $rec;
		}

		return $objects;
	}

	/**
	* Returns 100(%) for 1 and 150(%) for $max
	* y = a + mx
	* 100 = a + m (1)
	* 150 = a + m * max (2)
	* (1): a = 100 - m (3)
	* (2): a = 150 - max*m (4)
	* (3)&(4): m - 100 = max*m - 150 (5)
	* (5) 50 = (max-1)*m
	* => m = 50/(max -1)
	* => a = 100 - 50/(max -1)
	*/
	static function calculateFontSize($cnt, $max)
	{
		$m = ($max == 1)
			? 0
			: 50 / ($max - 1);
		$a = 100 - $m;
		$font_size = round($a + ($m * $cnt));
		return (int) $font_size;
	}
	
}

?>
