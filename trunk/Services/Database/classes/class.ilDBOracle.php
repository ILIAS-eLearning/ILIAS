<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once ("./Services/Database/classes/class.ilDB.php");

/**
* Oracle Database Wrapper
*
* This class extends the main ILIAS database wrapper ilDB. Only a few
* methods should be overwritten, that contain Oracle specific statements
* and methods.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDB.php 18989 2009-02-15 12:57:19Z akill $
* @ingroup ServicesDatabase
*/
class ilDBOracle extends ilDB
{
	const CLOB_BUFFER_SIZE = 2000;
	const LIMIT_EXPRESSIONS_IN_LIST = 1000;


	/**
	* Get DSN.
	*/
	function getDSN()
	{
		// TODO: check if there is another solution.
		// This works with 11g
		if(!isset($GLOBALS['_MDB2_dsninfo_default']['charset']) or		
			$GLOBALS['_MDB2_dsninfo_default']['charset'] != 'utf8')
		{
			$GLOBALS['_MDB2_dsninfo_default']['charset'] = 'utf8'; 
		} 

		return array('phptype' => 'oci8',
                        'hostspec' => $this->getDBHost(),
                        'username' => $this->getDBUser(),
                        'password' => $this->getDBPassword(),
						'port' => $this->getDBPort(),
                        'service' => $this->getDBName()
                        );
		
		//return "oci8://".$this->getDBUser().":".$this->getDBPassword()."@".
		//	$this->getDBHost()."/?service=".$this->getDBName();
	}

	/**
	* Get DB Type
	*/
	function getDBType()
	{
		return "oracle";
	}
	
	public function getDBVersion()
	{
		$query = 'SELECT * FROM v$version';
		$res = $this->db->query($query);
		
		if(MDB2::isError($res))
		{
			return parent::getDBVersion();
		}
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return isset($row['banner']) ? $row['banner'] : parent::getDBVersion();
	}
	
	/**
	* Get reserved words
	*/
	static function getReservedWords()
	{
		// version: 10g
		// url: http://download-west.oracle.com/docs/cd/B14117_01/server.101/b10759/ap_keywd.htm#g691972
		return array(
			"ACCESS", "ADD", "ALL", "ALTER", "AND", "ANY", "AS", "ASC",
			"AUDIT", "BETWEEN", "BY", "CHAR", "CHECK", "CLUSTER", "COLUMN",
			"COMMENT", "COMPRESS", "CONNECT", "CREATE", "CURRENT", "DATE",
			"DECIMAL", "DEFAULT", "DELETE", "DESC", "DISTINCT", "DROP", "ELSE",
			"EXCLUSIVE", "EXISTS", "FILE", "FLOAT", "FOR", "FROM", "GRANT", "GROUP",
			"HAVING", "IDENTIFIED", "IMMEDIATE", "IN", "INCREMENT", "INDEX", "INITIAL",
			"INSERT", "INTEGER", "INTERSECT", "INTO", "IS", "LEVEL", "LIKE", "LOCK", "LONG",
			"MAXEXTENTS", "MINUS", "MLSLABEL", "MODE", "MODIFY", "NOAUDIT", "NOCOMPRESS", "NOT",
			"NOWAIT", "NULL", "NUMBER", "OF", "OFFLINE", "ON", "ONLINE","OPTION",
			"OR", "ORDER", "PCTFREE", "PRIOR", "PRIVILEGES", "PUBLIC", "RAW", "RENAME",
			"RESOURCE", "REVOKE", "ROW", "ROWID", "ROWNUM", "ROWS", "SELECT", "SESSION", "SET",
			"SHARE", "SIZE", "SMALLINT", "START", "SUCCESSFUL", "SYNONYM", "SYSDATE","TABLE",
			"THEN", "TO", "TRIGGER", "UID", "UNION", "UNIQUE", "UPDATE", "USER","VALIDATE",
			"VALUES", "VARCHAR", "VARCHAR2", "VIEW", "WHENEVER", "WHERE", "WITH"
		);
	}

	/**
	* Standard way to connect to db
	*/
	function doConnect()
	{
		$this->db = MDB2::connect($this->getDSN(),
			array("use_transactions" => true));
	}

	/**
	* Initialize the database connection
	*/
	function initConnection()
	{
		$GLOBALS['_MDB2_dsninfo_default']['charset'] = 'utf8';
		$this->query("ALTER SESSION SET nls_length_semantics='CHAR'");
		$this->query("ALTER SESSION SET NLS_SORT = binary_ci");
	}

/*	function manipulate($sql)
	{
//echo "1";
//if (!is_int(strpos($sql, "frm_thread_access")))
//{
//echo "2";
		return parent::manipulate($sql);
//}
//echo "3";
	}*/

	/**
	* now()
	* @todo fix this
	*/
	function now()
	{
		return "LOCALTIMESTAMP";
	}
	
	/**
	* fromUnixtime (makes timestamp out of unix timestamp)
	*/
	function fromUnixtime($a_expr, $a_to_text = true)
	{
		if (!$a_to_text)
		{
			return "TO_DATE('19700101000000','YYYYMMDDHH24MISS') ".
				"+ NUMTODSINTERVAL(".$a_expr.", 'SECOND')";
		}
		else
		{
			return "to_char(TO_DATE('19700101000000','YYYYMMDDHH24MISS') ".
				"+ NUMTODSINTERVAL(".$a_expr.", 'SECOND'), 'yyyy-mm-dd hh24:mi:ss')";
		}
	}

	/**
	* Unix timestamp
	*/
	function unixTimestamp()
	{
		// sysdate should respect database time zone
		// current_date would respect session time zone
		return "ROUND((sysdate - to_date('01-JAN-1970','DD-MON-YYYY')) * (86400))";
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
			
			// integer auto-typecast (this casts bool values to integer) 
			if ($col[0] == 'integer' && !is_null($col[1]))
			{
				$col[1] = (int) $col[1];
			}

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
		if ($lobs)	// delete/insert
		{
			$this->manipulate("DELETE FROM ".$a_table." WHERE ".
				implode ($delwhere, " AND ")
				);
			$this->insert($a_table, $a_columns);
			
			//$r = $this->db->extended->autoExecute($a_table, $field_values, MDB2_AUTOQUERY_INSERT, null, $types);
			$this->handleError($r, "replace, delete/insert(".$a_table.")");
		}
		else	// if no lobs are used, use manipulate
		{
			$q = "MERGE INTO ".$a_table." a ".
				"USING (SELECT ".implode($val_field, ", ")." ".
				"FROM DUAL) b ON (".implode($abpk, " AND ").") ".
				"WHEN MATCHED THEN UPDATE SET ".implode($aboc, ", ")." ".
				"WHEN NOT MATCHED THEN INSERT (".implode($a, ",").") VALUES (".implode($b, ",").")";
			$r = $this->manipulate($q);
		}
		return $r;
	}
	
	/**
	 * Create locate string
	 * @param string $a_needle
	 * @param string $a_string
	 * @param int $a_start_pos [optional]
	 * @return 
	 */
	public function locate($a_needle,$a_string,$a_start_pos = 1)
	{
		$locate = ' INSTR( ';
		$locate .= ('SUBSTR('.$a_string.',0,'.self::CLOB_BUFFER_SIZE.')');
		$locate .= ',';
		$locate .= $a_needle;
		$locate .= ',';
		$locate .= $a_start_pos;
		$locate .= ') ';
		return $locate;
	}
	
	/**
	 * Provisional LIKE support for oracle CLOB's
	 * Uses SUBSTR to reduce the length.
	 * TODO: we can use <code>self::CLOB_BUFFER_SIZE = 4000</code> since
	 * since the maximum buffer is 4000 byte and not 4000 chars
	 * @param object $a_col
	 * @param object $a_type
	 * @param object $a_value [optional]
	 * @param object $case_insensitive [optional]
	 * @return 
	 */
	public function like($a_col, $a_type, $a_value = "?", $case_insensitive = true)
	{
		if($a_type == 'text')
		{
			return parent::like($a_col,$a_type,$a_value,$case_insensitive);
		}

		if (!in_array($a_type, array("text", "clob", "blob")))
		{
			$this->raisePearError("Like: Invalid column type '".$a_type."'.", $this->error_class->FATAL);
		}
		if ($a_value == "?")
		{
			if ($case_insensitive)
			{
				return "UPPER(SUBSTR(".$a_col.",0,".self::CLOB_BUFFER_SIZE.")) LIKE(UPPER(?))";
			}
			else
			{
				return "SUBSTR(".$a_col .",0,".self::CLOB_BUFFER_SIZE.") LIKE(?)";
			}
		}
		else
		{
			if ($case_insensitive)
			{
				return " UPPER(SUBSTR(".$a_col.",0,".self::CLOB_BUFFER_SIZE.")) LIKE(UPPER(".$this->quote($a_value, 'text')."))";
			}
			else
			{
				return " SUBSTR(".$a_col.",0,".self::CLOB_BUFFER_SIZE.") LIKE(".$this->quote($a_value, 'text').")";
			}
		}
	}
	
	/**
	 * CONCAT for oracle allows only the concatenation of two values 
	 * @param object $a_values
	 * @param object $a_allow_null [optional]
	 * @return 
	 */
	public function concat($a_values, $a_allow_null = true)
	{
		if(count($a_values) <= 2)
		{
			return parent::concat($a_values,false);
		}
		
		$first = true;
		foreach($a_values as $field_info)
		{
			if(!$first)
			{
				$concat_value = parent::concat(
					array(
						array($concat_value,$concat_type),
						array($field_info[0],$field_info[1])),
					false
				);
			}
			else
			{
				$first = false;
				$concat_value = $field_info[0];
				$concat_type = $field_info[1];
			}
		}
		return $concat_value;
	}
	
	/**
	* Overwritten implementation of <code>$ilDB->in</code>
	* to avoid ORA-01795 (maximum number of expressions in a list is 1000)
	* 
	* This implementation rewrites the in clause to 
	* <code>field IN (1,2,3,...,1000) OR field IN (1000,1001,...,2000)</code>
	*
	* Example:
	*	$ids = array(10,12,18);
	*	$st = $ilDB->prepare("SELECT * FROM table ".
	*		"WHERE ".$ilDB->in("id", $ids),
	*		$ilDB->addTypesToArray($types, "integer", count($ids)));
	*	$set = $ilDB->execute($st, $ids);
	*/
	function in($a_field, $a_values, $negate = false, $a_type = "")
	{
		if(count($a_values) <= self::LIMIT_EXPRESSIONS_IN_LIST)
		{
			return parent::in($a_field,$a_values,$negate,$a_type);
		}
		
		$first = true;
		$concat = $negate ? ' AND ' : ' OR ';
		$in = '(';
		do
		{
			if(!$first)
			{
				$in .= $concat;
				
			}
			$first = false;
			
			$spliced = array_splice($a_values, 0, self::LIMIT_EXPRESSIONS_IN_LIST);
			$in .= parent::in($a_field, $spliced, $negate, $a_type);
			
		}
		while($a_values);

		return $in." ) ";
	}
	
	
	/**
	 * Lock table
	 * 
	 * E.g $ilDB->lockTable('tree',ilDB::LOCK_WRITE,'t1')
	 * @param string $a_name
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
	
	/**
	* Drop a primary key from a table
	*
	* @param	string		table name
	*/
	function dropPrimaryKey($a_table)
	{
		// oracle 10: dropping constraint may not drop attached index
		// http://www.orafaq.com/forum/t/59807/0/
		
		$query = "ALTER TABLE ".$a_table." DROP PRIMARY KEY DROP INDEX";
		$res = $this->db->query($query);		
		
		return $this->handleError($res, "dropPrimaryKey(".$a_table.")");
	}
}
?>