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
* This class provides methods for building a DB generation script,
* getting a full overview on abstract table definitions and more...
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDBUpdate.php 18649 2009-01-21 09:59:23Z akill $
* @ingroup ServicesDatabase
*/
class ilDBGenerator
{
	var $whitelist = array();
	var $blacklist = array();
	var $tables = array();
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilDB;
		
		$this->manager = $ilDB->db->loadModule('Manager');
		$this->reverse = $ilDB->db->loadModule('Reverse');
		$this->il_db = $ilDB;
		include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		$this->analyzer = new ilDBAnalyzer();
	}
	
	/**
	* Set Table Black List.
	* (Tables that should not be included in the processing)
	*
	* @param	array	$a_blacklist	Table Black List
	*/
	function setBlackList($a_blacklist)
	{
		$this->blacklist = $a_blacklist;
	}

	/**
	* Get Table Black List.
	*
	* @return	array	Table Black List
	*/
	function getBlackList()
	{
		return $this->blacklist;
	}

	/**
	* Set Table White List.
	* Per default all tables are included in the processing. If a white
	* list ist provided, only them will be used.
	*
	* @param	array	$a_whitelist	Table White List
	*/
	function setWhiteList($a_whitelist)
	{
		$this->whitelist = $a_whitelist;
	}

	/**
	* Get Table White List.
	*
	* @return	array	Table White List
	*/
	function getWhiteList()
	{
		return $this->whitelist;
	}

	/**
	* Get (all) tables
	*/
	function getTables()
	{
		$r = $this->manager->listTables();
		if (!MDB2::isError($r))
		{
			$this->tables = $r;
		}
	}
	
	/**
	* Check whether a table should be processed or not
	*/
	function checkProcessing($a_table)
	{
		// check black list
		if (in_array($a_table, $this->blacklist))
		{
			return false;
		}
		
		// check white list
		if (count($this->whitelist) > 0 && !in_array($a_table, $this->whitelist))
		{
			return false;
		}
		
		return true;
	}
	
	/**
	* Build DB generation script
	*
	* @param	string		output filename, if no filename is given, script is echoed
	*/
	function buildDBGenerationScript($a_filename = "")
	{
		$file = "";
		if ($a_filename != "")
		{
			$file = fopen($a_filename, "w");
		}
		else
		{
			echo "<pre>";
		}
		
		$this->getTables();
		foreach ($this->tables as $table)
		{
			if ($this->checkProcessing($table))
			{
				// create table statement
				$this->buildCreateTableStatement($table, $file);
				
				// primary key
				$this->buildAddPrimaryKeyStatement($table, $file);
				
				// indices
				$this->buildAddIndexStatements($table, $file);
				
				// auto increment sequence
				$this->buildCreateSequenceStatement($table, $file);
				
				// inserts
				$this->buildInsertStatements($table, $file);
			}
		}
		
		if ($a_filename == "")
		{
			echo "</pre>";
		}

	}
	
	/**
	* Build CreateTable statement
	*
	* @param	string		table name
	* @param	file		file resource or empty string
	*/
	function buildCreateTableStatement($a_table, $a_file = "")
	{
		$fields = $this->analyzer->getFieldInformation($a_table);
		$this->fields = $fields;
		
		$create_st = "\n\n//\n// ".$a_table."\n//\n";
		$create_st.= '$fields = array ('."\n";
		$f_sep = "";
		foreach ($fields as $f => $def)
		{

			$create_st.= "\t".$f_sep.'"'.$f.'" => array ('."\n";
			$f_sep = ",";
			$a_sep = "";
			foreach ($def as $k => $v)
			{
				if ($k != "nativetype" && $k != "autoincrement" && !is_null($v))
				{
					switch ($k)
					{
						case "notnull":
						case "unsigned":
						case "fixed":
							$v = $v ? "true" : "false";
							break;
							
						case "default":
						case "type":
							$v = '"'.$v.'"';
							brak;
							
						default:
							break;
					}
					$create_st.= "\t\t".$a_sep.'"'.$k.'" => '.$v."\n";
					$a_sep = ",";
				}
			}
			$create_st.= "\t".')'."\n";
		}
		$create_st.= ');'."\n";
		$create_st.= '$ilDB->createTable("'.$a_table.'", $fields);'."\n";
		
		if ($a_file == "")
		{
			echo $create_st;
		}
	}
	
	/**
	* Build AddPrimaryKey statement
	*
	* @param	string		table name
	* @param	file		file resource or empty string
	*/
	function buildAddPrimaryKeyStatement($a_table, $a_file = "")
	{
		$pk = $this->analyzer->getPrimaryKeyInformation($a_table);

		if (is_array($pk["fields"]) && count($pk["fields"]) > 0)
		{
			$pk_st = "\n".'$pk_fields = array(';
			$sep = "";
			foreach ($pk["fields"] as $f => $pos)
			{
				$pk_st.= $sep.'"'.$f.'"';
				$sep = ",";
			}
			$pk_st.= ");\n";
			$pk_st.= '$ilDB->addPrimaryKey("'.$a_table.'", $pk_fields);'."\n";
			
			if ($a_file == "")
			{
				echo $pk_st;
			}
		}
	}

	/**
	* Build AddIndex statements
	*
	* @param	string		table name
	* @param	file		file resource or empty string
	*/
	function buildAddIndexStatements($a_table, $a_file = "")
	{
		$ind = $this->analyzer->getIndicesInformation($a_table);

		if (is_array($ind))
		{
			foreach ($ind as $i)
			{
				$in_st = "\n".'$in_fields = array(';
				$sep = "";
				foreach ($i["fields"] as $f => $pos)
				{
					$in_st.= $sep.'"'.$f.'"';
					$sep = ",";
				}
				$in_st.= ");\n";
				$in_st.= '$ilDB->addIndex("'.$a_table.'", $in_fields, "'.$i["name"].'");'."\n";
				
				if ($a_file == "")
				{
					echo $in_st;
				}
			}
		}
	}

	/**
	* Build CreateSequence statement
	*
	* @param	string		table name
	* @param	file		file resource or empty string
	*/
	function buildCreateSequenceStatement($a_table, $a_file = "")
	{
		$seq = $this->analyzer->hasSequence($a_table);
		if ($seq !== false)
		{
			$seq_st = "\n".'$ilDB->createSequence("'.$a_table.'", '.(int) $seq.');'."\n";

			if ($a_file == "")
			{
				echo $seq_st;
			}
		}
	}

	/**
	* Build Insert statements
	*
	* @param	string		table name
	* @param	file		file resource or empty string
	*/
	function buildInsertStatements($a_table, $a_file = "")
	{
		$st = $this->il_db->prepare("SELECT * FROM `".$a_table."`");
		$set = $this->il_db->execute($st, array());
		$ins_st = "";
		$first = true;
		while ($rec = $this->il_db->fetchAssoc($set))
		{
			if ($first)
			{
				$fields = array();
				$types = array();
				foreach ($rec as $f => $v)
				{
					$fields[] = "`".$f."`";
					$types[] = '"'.$this->fields[$f]["type"].'"';
				}
				$fields_str = "(".implode($fields, ",").")";
				$types_str = "array(".implode($types, ",").")";
				$ins_st = "\n".'$st = $ilDB->prepareManip("INSERT INTO `'.$a_table.'` '."\n";
				$ins_st.= "\t".$fields_str."\n";
				$ins_st.= "\t".'VALUES '."(?".str_repeat(",?", count($fields) - 1).')"'.",\n";
				$ins_st.= "\t".$types_str.');'."\n";
			}
			reset($rec);
			$values = array();
			foreach ($rec as $f => $v)
			{
				$values[] = '"'.$v.'"';
			}
			$values_str = "array(".implode($values, ",").")";
			$ins_st.= '$ilDB->execute($st,'.$values_str.');'."\n";
			
			$first = false;
			if ($a_file == "")
			{
				echo $ins_st;
			}
			$ins_st = "";
		}
	}

}
?>
