<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Advanced meta data adapter 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilGlossaryAdvMetaDataAdapter
{
	/**
	 * Constructor
	 */
	function __construct($a_glo_id)
	{
		$this->glo_id = $a_glo_id;
	}
	

	/**
	 * Get all advanced metadata fields
	 */
	function getAllFields()
	{
		$fields = array();
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		$recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("glo", $this->glo_id, "term");

		foreach($recs as $record_obj)
		{
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
			foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_obj->getRecordId()) as $def)
			{
				$fields[$def->getFieldId()] = array(
					"id" => $def->getFieldId(),
					"title" => $def->getTitle(),
					"type" => $def->getType()
					);
			}
		}

		return $fields;
	}
	
	/**
	 * Get column order
	 *
	 * @param
	 * @return
	 */
	function getColumnOrder()
	{
		global $ilDB, $lng;
		
		$columns = array();
		
		$set = $ilDB->query("SELECT * FROM glo_advmd_col_order ".
				" WHERE glo_id = ".$ilDB->quote($this->glo_id, "integer").
				" ORDER BY order_nr"
		);
		$order = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$order[$rec["field_id"]] = $rec["order_nr"];
		}
//var_dump($order);
		// add term at beginning, if not included
		if (!isset($order[0]))
		{
			$columns[] = array("id" => 0,
				"text" => $lng->txt("cont_term"));
		}

		$fields = $this->getAllFields();
		
		// add all fields that have been already sorted
		foreach ($order as $id => $order_nr)
		{
			if (isset($fields[$id]))
			{
				$columns[] = array("id" => $id,
					"text" => $fields[$id]["title"]);
				unset($fields[$id]);
			}
			else if ($id == 0)
			{
				$columns[] = array("id" => 0,
					"text" => $lng->txt("cont_term"));				
			}
		}
		
		// add all fields that have not been sorted
		foreach ($fields as $f)
		{
			$columns[] = array("id" => $f["id"],
				"text" => $f["title"]);
		}
		
		return $columns;
	}

	/**
	 * Save column order
	 *
	 * @param
	 * @return
	 */
	function saveColumnOrder($a_cols)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM glo_advmd_col_order WHERE ".
			" glo_id = ".$ilDB->quote($this->glo_id, "integer")
		);

		$nr = 10;
		$set = array();
		foreach ($a_cols as $c)
		{
//var_dump($c);
			if (!isset($set[$c["id"]]))
			{
				$ilDB->manipulate("INSERT INTO glo_advmd_col_order ".
						"(glo_id, field_id, order_nr) VALUES (".
						$ilDB->quote($this->glo_id, "integer").",".
						$ilDB->quote($c["id"], "integer").",".
						$ilDB->quote($nr+=10, "integer").
						")");
				$set[$c["id"]] = $c["id"];
			}
		}
	}


}

?>
