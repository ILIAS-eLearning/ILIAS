<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		
		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}
		
		$ilDB->manipulate("DELETE FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id, "integer")." AND ".
			"obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type, "text")." AND ".
			"sub_obj_id = ".$ilDB->quote((int) $a_sub_obj_id, "integer")." AND ".
			$ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true));
			//"sub_obj_type = ".$ilDB->quote($a_sub_obj_type, "text"));
		
		if (is_array($a_tags))
		{
			$inserted = array();
			foreach($a_tags as $tag)
			{
				if (!in_array(strtolower($tag), $inserted))
				{
					$tag = str_replace(" ", "_", trim($tag));
					$ilDB->manipulate("INSERT INTO il_tag (user_id, obj_id, obj_type,".
						"sub_obj_id, sub_obj_type, tag) VALUES (".
						$ilDB->quote($a_user_id, "integer").",".
						$ilDB->quote($a_obj_id, "integer").",".
						$ilDB->quote($a_obj_type, "text").",".
						$ilDB->quote((int) $a_sub_obj_id, "integer").",".
						$ilDB->quote($a_sub_obj_type, "text").",".
						$ilDB->quote($tag, "text").")");

					$inserted[] = strtolower($tag);
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

		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}
		
		$q = "SELECT * FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id, "integer")." AND ".
			"obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type, "text")." AND ".
			"sub_obj_id = ".$ilDB->quote((int) $a_sub_obj_id, "integer")." AND ".
			$ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true).
			" ORDER BY tag ASC";
		$set = $ilDB->query($q);
		$tags = array();
		while($rec = $ilDB->fetchAssoc($set))
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
	static function getTagsForObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type,
		$a_only_online = true)
	{
		global $ilDB;
		
		$online_str = ($a_only_online)
			? $online_str = " AND is_offline = ".$ilDB->quote(0, "integer")." "
			: "";

		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}
		
		$q = "SELECT count(user_id) as cnt, tag FROM il_tag WHERE ".
			"obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			"obj_type = ".$ilDB->quote($a_obj_type, "text")." AND ".
			"sub_obj_id = ".$ilDB->quote($a_sub_obj_id, "integer")." AND ".
			$ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true).
			$online_str.
			"GROUP BY tag ORDER BY tag ASC";
		$set = $ilDB->query($q);
		$tags = array();
		while($rec = $ilDB->fetchAssoc($set))
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
	static function getTagsForUser($a_user_id, $a_max = 0, $a_only_online = true)
	{
		global $ilDB;

		$online_str = ($a_only_online)
			? $online_str = " AND is_offline = ".$ilDB->quote(0, "integer")." "
			: "";

		$set = $ilDB->query("SELECT count(*) as cnt, tag FROM il_tag WHERE ".
			"user_id = ".$ilDB->quote($a_user_id, "integer")." ".
			$online_str.
			" GROUP BY tag ORDER BY cnt DESC");
		$tags = array();
		$cnt = 1;
		while(($rec = $ilDB->fetchAssoc($set)) &&
			($a_max == 0 || $cnt <= $a_max))
		{
			$tags[] = $rec;
			$cnt++;
		}
		$tags = ilUtil::sortArray($tags, "tag", "asc");

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
			"user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND tag = ".$ilDB->quote($a_tag, "text");

		$set = $ilDB->query($q);
		$objects = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (ilObject::_exists($rec["obj_id"]))
			{
				if ($rec["sub_obj_type"] == "-")
				{
					$rec["sub_obj_type"] = "";
				}
				$objects[] = $rec;
			}
			else
			{
				ilTagging::deleteTagsOfObject($rec["obj_id"], $rec["obj_type"],
					$rec["sub_obj_id"], $rec["sub_obj_type"]);
			}
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
	
	/**
	* Set offline
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	* @param	boolean		$a_offline			Offline (true/false, true means offline!)
	*/
	static function setTagsOfObjectOffline($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type,
		$a_offline = true)
	{
		global $ilDB;

		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}
		
		$ilDB->manipulateF("UPDATE il_tag SET is_offline = %s ".
			"WHERE ".
			"obj_id = %s AND ".
			"obj_type = %s AND ".
			"sub_obj_id = %s AND ".
			$ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true),
			array("boolean", "integer", "text", "integer"),
			array($a_offline, $a_obj_id, $a_obj_type, $a_sub_obj_id));
	}

	/**
	* Deletes tags of an object
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	*/
	static function deleteTagsOfObject($a_obj_id, $a_obj_type, $a_sub_obj_id, $a_sub_obj_type)
	{
		global $ilDB;

		if ($a_sub_obj_type == "")
		{
			$a_sub_obj_type = "-";
		}
		
		$ilDB->manipulateF("DELETE FROM il_tag ".
			"WHERE ".
			"obj_id = %s AND ".
			"obj_type = %s AND ".
			"sub_obj_id = %s AND ".
			$ilDB->equals("sub_obj_type", $a_sub_obj_type, "text", true),
			array("integer", "text", "integer"),
			array($a_obj_id, $a_obj_type, $a_sub_obj_id));
	}

}

?>
