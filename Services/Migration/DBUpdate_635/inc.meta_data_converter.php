<?php

function ilMDConvert($table,$fields,$key)
{
	global $ilDB;

	$where = "WHERE ";
	$where .= (implode(" LIKE '%\%' ESCAPE '§' OR ",$fields));
	$where .= " LIKE '%\\%' ESCAPE '§'";

	$query = "SELECT * FROM $table ".
		$where;

	$res = $ilDB->query($query);
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$query = "UPDATE $table ";

		$counter = 0;
		foreach($fields as $field)
		{
			if($counter++)
				$query .= ", ";
			else
				$query .= "SET ";

			$query .= ($field ." = '".addslashes(stripslashes($row->$field))."'");
		}
		$query .= (" WHERE $key = ".$row->$key);

		// Perform the query
		$ilDB->query($query);
	}
}
