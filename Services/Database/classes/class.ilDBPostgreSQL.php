<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once ("./Services/Database/classes/class.ilDB.php");

/**
* PostreSQL Database Wrapper
*
* This class extends the main ILIAS database wrapper ilDB. Only a few
* methods should be overwritten, that contain PostreSQL specific statements
* and methods.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDB.php 18989 2009-02-15 12:57:19Z akill $
* @ingroup ServicesDatabase
*/
class ilDBPostgreSQL extends ilDB
{

	/**
	* Get DSN.
	*/
	function getDSN()
	{
		return "pgsql://".$this->getDBUser().":".$this->getDBPassword()."@".
			$this->getDBHost()."/".$this->getDBName();
	}

	/**
	* Get DB Type
	*/
	function getDBType()
	{
		return "postgres";
	}
	
	/**
	* Get reserved words
	*/
	static function getReservedWords()
	{
		// version: 8.3.6
		// url: http://www.postgresql.org/docs/current/static/sql-keywords-appendix.html
		return array(
			"ALL", "ANALYSE", "ANALYZE", "AND", "ANY", "ARRAY",
			"AS", "ASC", "ASYMMETRIC", "AUTHORIZATION", "BETWEEN", "BINARY", "BOTH",
			"CASE", "CAST", "CHECK", "COLLATE", "COLUMN", "CONSTRAINT", "CREATE",
			"CROSS", "CURRENT_DATE", "CURRENT_ROLE", "CURRENT_TIME", "CURRENT_TIMESTAMP", "CURRENT_USER", "DEFAULT",
			"DEFERRABLE", "DESC", "DISTINCT", "DO", "ELSE", "END", "EXCEPT",
			"FALSE", "FOR", "FOREIGN", "FREEZE", "FROM", "FULL", "GRANT",
			"GROUP", "HAVING", "ILIKE", "IN", "INITIALLY", "INNER", "INTERSECT",
			"INTO", "IS", "ISNULL", "JOIN", "LEADING", "LEFT", "LIKE",
			"LIMIT", "LOCALTIME", "LOCALTIMESTAMP", "NATURAL", "NEW", "NOT", "NOTNULL",
			"NULL", "OFF", "OFFSET", "OLD", "ON", "ONLY", "OR",
			"ORDER", "OUTER", "OVERLAPS", "PLACING", "PRIMARY", "REFERENCES", "RETURNING",
			"RIGHT", "SELECT", "SESSION_USER", "SIMILAR", "SOME", "SYMMETRIC", "TABLE",
			"THEN", "TO", "TRAILING", "TRUE", "UNION", "UNIQUE", "USER",
			"USING", "VERBOSE", "WHEN", "WHERE", "WITH"
		);
	}

	/**
	* Initialize the database connection
	*/
	function initConnection()
	{
	}

	/**
	* now()
	* @todo fix this
	*/
	function now()
	{
		return "now()";
	}
	
	/**
	 * Constraint names must be "globally" unique in oracle.
	 */
	function constraintName($a_table, $a_constraint)
	{
		return $a_table."_".$a_constraint;
	}

	/**
	 * Primary key identifier
	 */
	function getPrimaryKeyIdentifier()
	{
		return "pk";
	}

	/**
	* Is fulltext index supported?
	*/
	function supportsFulltext()
	{
		return false;
	}
	
	/**
	* Replace into method.
	*
	* @param	string		table name
	* @param	array		primary key values: array("field1" => array("text", $name), "field2" => ...)
	* @param	array		other values: array("field1" => array("text", $name), "field2" => ...)
	*/
	function replace($a_table, $a_pk_columns, $a_other_columns)
	{
		$a_columns = array_merge($a_pk_columns, $a_other_columns);
		$fields = array();
		$field_values = array();
		$placeholders = array();
		$types = array();
		$values = array();
		$lobs = false;
		$lob = array();
		$val_field = array();
		$a = array();
		$b = array();
		foreach ($a_columns as $k => $col)
		{
			if($col[0] == 'clob' or $col[0] == 'blob')
			{
				$val_field[] = $this->quote($col[1], 'text')." ".$k;
			}
			else
			{
				$val_field[] = $this->quote($col[1], $col[0])." ".$k;
			}
			$fields[] = $k;
			$placeholders[] = "%s";
			$placeholders2[] = ":$k";
			$types[] = $col[0];
			$values[] = $col[1];
			$field_values[$k] = $col[1];
			if ($col[0] == "blob" || $col[0] == "clob")
			{
				$lobs = true;
				$lob[$k] = $k;
			}
			$a[] = "a.".$k;
			$b[] = "b.".$k;
		}
		$abpk = array();
		$aboc = array();
		$delwhere = array();
		foreach ($a_pk_columns as $k => $col)
		{
			$abpk[] = "a.".$k." = b.".$k;
			$delwhere[] = $k." = ".$this->quote($col[1], $col[0]);
		}
		foreach ($a_other_columns as $k => $col)
		{
			$aboc[] = "a.".$k." = b.".$k;
		}
//		if ($lobs)	// lobs -> use prepare execute (autoexecute broken in PEAR 2.4.1)
//		{
			$this->manipulate("DELETE FROM ".$a_table." WHERE ".
				implode ($delwhere, " AND ")
				);
			$this->insert($a_table, $a_columns);
			
			//$r = $this->db->extended->autoExecute($a_table, $field_values, MDB2_AUTOQUERY_INSERT, null, $types);
			$this->handleError($r, "replace, delete/insert(".$a_table.")");
//		}
/*		else	// if no lobs are used, use manipulate
		{
			$q = "MERGE INTO ".$a_table." a ".
				"USING (SELECT ".implode($val_field, ", ")." ".
				"FROM DUAL) b ON (".implode($abpk, " AND ").") ".
				"WHEN MATCHED THEN UPDATE SET ".implode($aboc, ", ")." ".
				"WHEN NOT MATCHED THEN INSERT (".implode($a, ",").") VALUES (".implode($b, ",").")";
			$r = $this->manipulate($q);
		}*/
		return $r;
	}
	
	/**
	 * Lock table
	 * 
	 * E.g $ilDB->lockTable('tree',ilDB::LOCK_WRITE,'t1')
	 * @param array $a_tables
	 * @param int $a_mode
	 * @param string $a_alias
	 * @return 
	 */
	public function lockTables($a_tables)
	{
		global $ilLog;
		
		$locks = array();

		$counter = 0;
		foreach($a_tables as $table)
		{
			$lock = 'LOCK TABLE ';

			$lock .= ($table['name'].' ');

			switch($table['type'])
			{
				case ilDB::LOCK_READ:
					$lock .= ' IN SHARE MODE ';
					break;
				
				case ilDB::LOCK_WRITE:
					$lock .= ' IN EXCLUSIVE MODE ';
					break;
			}
			
			$locks[] = $lock;
		}

		// @TODO use and store a unique identifier to allow nested lock/unlocks
		$this->db->beginTransaction();
		foreach($locks as $lock)
		{
			$this->db->query($lock);
			$ilLog->write(__METHOD__.': '.$lock);
		}
		return true;
	}
	
	/**
	 * Unlock tables
	 * @return 
	 */
	public function unlockTables()
	{
		$this->db->commit();
	}

}
?>
