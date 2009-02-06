<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* This class includes methods that help to abstract ILIAS 3.10.x MySQL tables
* for the use with MDB2 abstraction layer and full compliance mode support.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDBUpdate.php 18649 2009-01-21 09:59:23Z akill $
* @ingroup ServicesDatabase
*/
class ilMySQLAbstraction
{
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilDB;
		
		$this->il_db = $ilDB;
		$this->manager = $ilDB->db->loadModule('Manager');
		$this->reverse = $ilDB->db->loadModule('Reverse');
		$this->analyzer = new ilDBAnalyzer();
		$this->setTestMode(false);
	}

	/**
	* Set Test Mode.
	*
	* @param	boolean	$a_testmode	Test Mode
	*/
	function setTestMode($a_testmode)
	{
		$this->testmode = $a_testmode;
	}

	/**
	* Get Test Mode.
	*
	* @return	boolean	Test Mode
	*/
	function getTestMode()
	{
		return $this->testmode;
	}

	/**
	* Converts an existing (MySQL) ILIAS table in an abstract table.
	* This means the table conforms to the MDB2 field types, uses
	* sequences instead of auto_increment.
	*
	* @param	string		table name
	*/
	function performAbstraction($a_table_name, $a_set_text_fields_notnull_false = true)
	{
		// to do: log this procedure
		
		// convert table name to lowercase
		if (!$this->getTestMode())
		{
			$this->lowerCaseTableName($a_table_name);
			$a_table_name = strtolower($a_table_name);
			$this->storeStep($a_table_name, 10);
		}
		
		// get auto increment information
		$auto_inc_field = $this->analyzer->getAutoIncrementField($a_table_name);

		// get primary key information
		$pk = $this->analyzer->getPrimaryKeyInformation($a_table_name);

		// get indices information
		$indices = $this->analyzer->getIndicesInformation($a_table_name);
		
		// get field information
		$fields = $this->analyzer->getFieldInformation($a_table_name);
		
		if (!$this->getTestMode())
		{
			// remove auto increment
			$this->removeAutoIncrement($a_table_name, $auto_inc_field, $fields);
			$this->storeStep($a_table_name, 20);
	
			// remove primary key
			$this->removePrimaryKey($a_table_name, $pk);
			$this->storeStep($a_table_name, 30);
	
			// remove indices (@todo: fulltext index handling)
			$this->removeIndices($a_table_name, $indices);
			$this->storeStep($a_table_name, 40);
		}
		
		// alter table using mdb2 field types
		$this->alterTable($a_table_name, $fields, $a_set_text_fields_notnull_false, $pk);
		if ($this->getTestMode())
		{
			$a_table_name = strtolower($a_table_name)."_copy";
		}
		else
		{
			$this->storeStep($a_table_name, 50);
		}

		// lower case field names
		$this->lowerCaseColumnNames($a_table_name);
		if (!$this->getTestMode())
		{
			$this->storeStep($a_table_name, 60);
		}
		
		// add primary key
		$this->addPrimaryKey($a_table_name, $pk);
		if (!$this->getTestMode())
		{
			$this->storeStep($a_table_name, 70);
		}

		// add indices
		$this->addIndices($a_table_name, $indices);
		if (!$this->getTestMode())
		{
			$this->storeStep($a_table_name, 80);
		}
		
		// add "auto increment" sequence
		if ($auto_inc_field != "")
		{
			$this->addAutoIncrementSequence($a_table_name, $auto_inc_field);
		}
		if (!$this->getTestMode())
		{
			$this->storeStep($a_table_name, 90);
		}
		
		// replace empty strings with null values in text fields
		$this->replaceEmptyStringsWithNull($a_table_name);
		if (!$this->getTestMode())
		{
			$this->storeStep($a_table_name, 100);
		}
	}
	
	/**
	* Store performed step
	*/
	function storeStep($a_table, $a_step)
	{
		$st = $this->il_db->prepareManip("REPLACE INTO abstraction_progress (table_name, step)".
			" VALUES (?,?)", array("text", "integer"));
		$this->il_db->execute($st, array($a_table, $a_step));
	}
	
	/**
	* Replace empty strings with null values
	*/
	function replaceEmptyStringsWithNull($a_table)
	{
		global $ilDB;
		
		$fields = $this->analyzer->getFieldInformation($a_table);
		$upfields = array();
		foreach ($fields as $field => $def)
		{
			if ($def["type"] == "text" && $def["fixed"] == false &&
				($def["length"] >= 1 && $def["length"] <= 4000))
			{
				$upfields[] = $field;
			}
		}
		foreach ($upfields as $uf)
		{
			$ilDB->query("UPDATE `".$a_table."` SET `".$uf."` = null WHERE `".$uf."` = ''");
		}
	}
	
	/**
	* Lower case table and field names
	*
	* @param	string	table name
	*/
	function lowerCaseTableName($a_table_name)
	{
		global $ilDB;
		
		if ($a_table_name != strtolower($a_table_name))
		{
			// this may look strange, but it does not work directly
			// (seems that mysql does not see no difference whether upper or lowercase characters are used
			mysql_query("ALTER TABLE `".$a_table_name."` RENAME `".strtolower($a_table_name)."xxx"."`");
			mysql_query("ALTER TABLE `".strtolower($a_table_name)."xxx"."` RENAME `".strtolower($a_table_name)."`");
		}
	}
	
	/**
	* lower case column names
	*/
	function lowerCaseColumnNames($a_table_name)
	{
		global $ilDB;
		
		$result = mysql_query("SHOW COLUMNS FROM `".$a_table_name."`");
		while ($row = mysql_fetch_assoc($result))
		{
			if ($row["Field"] != strtolower($row["Field"]))
			{
				$ilDB->renameTableColumn($a_table_name, $row["Field"], strtolower($row["Field"]));
			}
		}

	}
	
	/**
	* Remove auto_increment attribute of a field
	*
	* @param	string		table name
	* @param	string		autoincrement field
	*/
	function removeAutoIncrement($a_table_name, $a_auto_inc_field)
	{
		if ($a_auto_inc_field != "")
		{
			$this->il_db->modifyTableColumn($a_table_name, $a_auto_inc_field, array());
		}
	}
	
	/**
	* Remove primary key from table
	*
	* @param	string		table name
	* @param	array		primary key information
	*/
	function removePrimaryKey($a_table, $a_pk)
	{
		if ($a_pk["name"] != "")
		{
			$this->il_db->dropPrimaryKey($a_table, $a_pk["name"]);
		}
	}

	/**
	* Remove Indices
	*
	* @param	string		table name
	* @param	array		indices information
	*/
	function removeIndices($a_table, $a_indices)
	{
		if (is_array($a_indices))
		{
			foreach($a_indices as $index)
			{
				$this->il_db->query("ALTER TABLE `".$a_table."` DROP INDEX `".$index["name"]."`");
			}
		}
	}
	
	/**
	* Use abstract types as delivered by MDB2 to alter table
	* and make it use only MDB2 known types.
	*
	* @param	string		table name
	* @param	array		fields information
	*/
	function alterTable($a_table, $a_fields, $a_set_text_fields_notnull_false = true, $pk = "")
	{
		$n_fields = array();
		foreach ($a_fields as $field => $d)
		{
			$def = $this->reverse->getTableFieldDefinition($a_table, $field);
			$best_alt = $this->analyzer->getBestDefinitionAlternative($def);
			$def = $def[$best_alt];

			// remove "current_timestamp" default for timestamps (not supported)
			if (strtolower($def["nativetype"]) == "timestamp" &&
				strtolower($def["default"]) == "current_timestamp")
			{
				unset($def["default"]);
			}
			
			// remove all invalid attributes
			foreach ($def as $k => $v)
			{
				if (!in_array($k, array("type", "default", "notnull", "length", "unsigned", "fixed")))
				{
					unset($def[$k]);
				}
			}
			
			// determine length for decimal type
			if ($def["type"] == "decimal")
			{
				$l_arr = explode(",",$def["length"]);
				$def["length"] = $l_arr[0];
			}
			
			// remove lenght values for float
			if ($def["type"] == "float")
			{
				unset($def["length"]);
			}
			
			// set notnull to false for text fields
			if ($a_set_text_fields_notnull_false && $def["type"] == "text" &&
				(!is_array($pk) || !isset($field,$pk["fields"][$field])))
			{
				$def["notnull"] = false;
			}
			
			// set unsigned to false for integers
			if ($def["type"] == "integer")
			{
				$def["unsigned"] = false;
			}

			$a = array();
			foreach ($def as $k => $v)
			{
				$a[$k] = $v;
			}
			$def["definition"] = $a;

			$n_fields[$field] = $def;
		}
		
		$changes = array(
			"change" => $n_fields
			);
//var_dump($n_fields);
		if (!$this->getTestMode())
		{
			$r = $this->manager->alterTable($a_table, $changes, false);
		}
		else
		{
			$r = $this->manager->createTable(strtolower($a_table)."_copy", $n_fields);
		}

		if (MDB2::isError($r))
		{
			//$err = "<br>Details: ".mysql_error();
			var_dump($r);
		}
		else
		{
			return $r;
		}
	}
	
	/**
	* Add primary key
	*
	* @param	string		table name
	* @param	array		primary key information
	*/
	function addPrimaryKey($a_table, $a_pk)
	{
		if (is_array($a_pk["fields"]))
		{
			$fields = array();
			foreach ($a_pk["fields"] as $f => $pos)
			{
				$fields[] = strtolower($f);
			}
			$this->il_db->addPrimaryKey($a_table, $fields);
		}
	}

	/**
	* Add indices
	*
	* @param	string		table name
	* @param	array		indices information
	*/
	function addIndices($a_table, $a_indices)
	{
		if (is_array($a_indices))
		{
			foreach ($a_indices as $index)
			{
				if (is_array($index["fields"]))
				{
					$fields = array();
					foreach ($index["fields"] as $f => $pos)
					{
						$fields[] = strtolower($f);
					}
					$this->il_db->addIndex($a_table, $fields, strtolower($index["name"]));
				}
			}
		}
	}
	
	/**
	* Add autoincrement sequence
	*
	* @param	string		table name
	* @param	string		autoincrement field
	*/
	function addAutoIncrementSequence($a_table, $a_auto_inc_field)
	{
		if ($a_auto_inc_field != "")
		{
			$set = $this->il_db->query("SELECT MAX(`".strtolower($a_auto_inc_field)."`) ma FROM `".$a_table."`");
			$rec = $this->il_db->fetchAssoc($set);
			$next = $rec["ma"] + 1;
			$this->il_db->createSequence($a_table, $next);
		}
	}
}
?>
