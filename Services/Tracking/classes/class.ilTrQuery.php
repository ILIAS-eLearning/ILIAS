<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tracking query class. Put any complex queries into this class. Keep 
 * tracking class small.
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrQuery
{
	/**
	* Get data for user administration list.
	*/
	static function getDataForObject($a_obj_id, $a_order_field, $a_order_dir, $a_offset, $a_limit,
		$a_filter = array(), $a_additional_fields = "")
	{
		global $ilDB, $rbacreview;
		
		$fields = array("usr_data.usr_id", "login");
		
		if (is_array($a_additional_fields))
		{
			$fields = array_merge($fields, $a_additional_fields);
		}
		
		// count query
		$count_query = "SELECT count(usr_data.usr_id) cnt".
			" FROM read_event JOIN usr_data ON (read_event.usr_id = usr_data.usr_id)";
			
		// basic query
		$query = "SELECT ".implode($fields, ",").
			" FROM read_event JOIN usr_data ON (read_event.usr_id = usr_data.usr_id)";
			
		// filter
		$query.= " WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");
		$count_query.= " WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");
		$query.= " AND usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");
		$count_query.= " AND usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");
		$where = " AND";
		
		if ($a_filter["string"] != "")		// email, name, login
		{
			$add = $where." (".$ilDB->like("usr_data.login", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.firstname", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.lastname", "text", $a_filter["string"]."%")." ".
				"OR ".$ilDB->like("usr_data.email", "text", "%".$a_filter["string"]."%").") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		// order by
		if (!in_array($a_order_field, $fields))
		{
			$a_order_field = "login";
		}
		if ($a_order_dir != "asc" && $a_order_dir != "desc")
		{
			$a_order_dir = "asc";
		}
		$query.= " ORDER BY ".$a_order_field." ".strtoupper($a_order_dir);
		
		// count query
		$set = $ilDB->query($count_query);
		$cnt = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt = $rec["cnt"];
		}
		
		$offset = (int) $a_offset;
		$limit = (int) $a_limit;
		$ilDB->setLimit($limit, $offset);
//var_dump($query);
		// set query
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}
		return array("cnt" => $cnt, "set" => $result);
	}	
}
