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
		include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		$this->analyzer = new ilDBAnalyzer();
	}

	/**
	* Converts an existing (MySQL) ILIAS table in an abstract table.
	* This means the table conforms to the MDB2 field types, uses
	* sequences instead of auto_increment.
	*
	* @param	string		table name
	*/
	function performAbstraction($a_table_name)
	{
		// to do: log this procedure
		
		// get auto increment information
		$auto_inc_field = $this->analyzer->getAutoIncrementField($a_table_name);

		// get primary key information
		$pk = $this->analyzer->getPrimaryKeyInformation($a_table_name);
		
		// get indices information
		$indices = $this->analyzer->getIndicesInformation($a_table_name);
		
		// get field information
		$fields = $this->analyzer->getFieldInformation($a_table_name);

		// remove auto increment
		$this->removeAutoIncrement($a_table_name, $auto_inc_field, $fields);

		// remove primary key
		$this->removePrimaryKey($a_table_name, $pk);

		// remove indices
		$this->removeIndices($a_table_name, $indices);
				
		// alter table using mdb2 field types
		$this->alterTable($a_table_name, $fields);
		
		// add primary key
		$this->addPrimaryKey($a_table_name, $pk);

		// add indices
		$this->addIndices($a_table_name, $indices);
		
		// add "auto increment" sequence
		if ($auto_inc_field != "")
		{
			$this->addAutoIncrementSequence($a_table_name, $auto_inc_field);
		}
	}
	
	/**
	* Remove auto_increment attribute of a field
	*/
	function removeAutoIncrement($a_table_name, $a_auto_inc_field, $a_fields)
	{
		if ($a_auto_inc_field != "")
		{
			$this->il_db->modifyTableField($a_table_name, $a_auto_inc_field,
				array("autoincrement" => false));
		}
	}
	
	/**
	* Remove primary key from table
	*
	* @param	string		table name
	*/
	function removePrimaryKey($a_table_name, $a_pk)
	{
		if ($a_pk["name"] != "")
		{
			$this->il_db->dropPrimaryKey($a_table_name, $a_pk["name"]);
		}
	}

	function removeIndices($a_table_name, $a_indices)
	{
	}
	
	/**
	* Use abstract types as delivered by MDB2 to alter table
	* and make it use only MDB2 known types.
	*/
	function alterTable($a_table, $a_fields)
	{
		$n_fields = array();
		foreach ($a_fields as $field => $d)
		{
			$def = $this->reverse->getTableFieldDefinition($a_table, $field);
			$def = $def[0];

			// remove "current_timestamp" default for timestamps (not supported)
			if (strtolower($def["nativetype"]) == "timestamp" &&
				strtolower($def["default"]) == "current_timestamp")
			{
				unset($def["default"]);
			}
			unset($def["nativetype"]);
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

		$r = $this->manager->alterTable($a_table, $changes, false);

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
	
	function addPrimaryKey($a_table_name, $a_indices)
	{
	}

	function addIndices($a_table_name, $a_indices)
	{
	}
	
	function addAutoIncrementSequence($a_table_name, $a_auto_inc_field)
	{
	}
}
?>
