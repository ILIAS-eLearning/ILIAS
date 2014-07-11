<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/interfaces/interface.ilDatabaseHandler.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilPDOToilDBAdapter implements ilDatabaseHandler
{
	/**
	 * @var PDO
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @param PDO $db
	 */
	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	/**
	 * @param mixed|PDOStatement $result
	 * @return null|array
	 */
	public function fetchAssoc($result)
	{
		if(!$result)
		{
			return null;
		}

		return $result->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @param mixed|PDOStatement $result
	 * @return null|stdClass
	 */
	public function fetchObject($result)
	{
		if(!$result)
		{
			return null;
		}

		return $result->fetch(PDO::FETCH_OBJ);
	}

	/**
	 * @param string $query
	 * @return mixed|PDOStatement
	 */
	public function query($query)
	{
		$lo = '';
		if($this->limit)
		{
			$lo = ' LIMIT ' . $this->limit;

			if($this->offset)
			{
				$lo .= ' OFFSET ' . $this->offset;
			}
		}

		$result = $this->db->query($query . $lo);

		$this->setLimit(0, 0);

		return $result;
	}

	/**
	 * @param string $query
	 * @return int
	 */
	public function manipulate($query)
	{
		return $this->db->exec($query);
	}

	/**
	 * @param string $query
	 * @param array  $types
	 * @param array  $values
	 * @return PDOStatement
	 */
	public function queryF($query, $types, $values)
	{
		$quoted_values = array();
		foreach($types as $k => $t)
		{
			$quoted_values[] = $this->quote($values[$k], $t);
		}
		$query = vsprintf($query, $quoted_values);

		return $this->query($query);
	}

	/**
	 * @param string $query
	 * @param array  $types
	 * @param array  $values
	 * @return int
	 */
	public function manipulateF($query, $types, $values)
	{
		$quoted_values = array();
		foreach($types as $k => $t)
		{
			$quoted_values[] = $this->quote($values[$k], $t);
		}
		$query = vsprintf($query, $quoted_values);

		return $this->manipulate($query);
	}

	/**
	 * @param   string $string
	 * @param string   $type
	 * @return string
	 */
	public function quote($string, $type = '')
	{
		return $this->db->quote($string);
	}

	/**
	 * @param   string $field
	 * @param   array  $values
	 * @param bool     $negate
	 * @param string   $type
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = '')
	{
		if(count($values) == 0)
		{
			return " 1=2 ";
		}
		if($type == "")
		{
			$str = $field . (($negate) ? " NOT" : "") . " IN (?" . str_repeat(",?", count($values) - 1) . ")";
		}
		else
		{
			$str = $field . (($negate) ? " NOT" : "") . " IN (";
			$sep = "";
			foreach($values as $v)
			{
				$str .= $sep . $this->quote($v, $type);
				$sep = ",";
			}
			$str .= ")";
		}

		return $str;
	}

	/**
	 * @param    string $field
	 * @param    string $type
	 * @param string    $value
	 * @param bool      $case_insensitive
	 * @return string
	 */
	public function like($field, $type, $value = "?", $case_insensitive = true)
	{
		if($value == "?")
		{
			if($case_insensitive)
			{
				return "UPPER(" . $field . ") LIKE(UPPER(?))";
			}
			else
			{
				return $field . " LIKE(?)";
			}
		}
		else
		{
			if($case_insensitive)
			{
				return " UPPER(" . $field . ") LIKE(UPPER(" . $this->quote($value, 'text') . "))";
			}
			else
			{
				return " " . $field . " LIKE(" . $this->quote($value, 'text') . ")";
			}
		}
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 */
	public function setLimit($limit, $offset = null)
	{
		$limit       = (int)$limit;
		$this->limit = $limit;
		if(!is_null($offset))
		{
			$offset       = (int)$offset;
			$this->offset = $offset;
		}
	}

	/**
	 * @param string $table
	 * @param array  $what
	 * @return int
	 */
	public function insert($table, $what)
	{
		$fields       = array();
		$field_values = array();
		$placeholders = array();
		$types        = array();
		$values       = array();
		$lobs         = false;
		$lob          = array();
		foreach($what as $k => $col)
		{
			$fields[]         = $k;
			$placeholders[]   = "%s";
			$placeholders2[]  = ":$k";
			$types[]          = $col[0];
			$values[]         = $col[1];
			$field_values[$k] = $col[1];
			if($col[0] == "blob" || $col[0] == "clob")
			{
				$lobs    = true;
				$lob[$k] = $k;
			}
		}

		$q = "INSERT INTO " . $table . " (" . implode($fields, ",") . ") VALUES (" .
			implode($placeholders, ",") . ")";
		return $this->manipulateF($q, $types, $values);
	}

	/**
	 * @param string $table
	 * @param array  $what
	 * @param array  $where
	 * @return int
	 */
	public function update($table, $what, $where)
	{
		$fields       = array();
		$field_values = array();
		$placeholders = array();
		$types        = array();
		$values       = array();
		$lobs         = false;
		$lob          = array();
		foreach($what as $k => $col)
		{
			$fields[]         = $k;
			$placeholders[]   = "%s";
			$placeholders2[]  = ":$k";
			$types[]          = $col[0];
			$values[]         = $col[1];
			$field_values[$k] = $col[1];
			if($col[0] == "blob" || $col[0] == "clob")
			{
				$lobs    = true;
				$lob[$k] = $k;
			}
		}

		foreach($where as $k => $col)
		{
			$types[]          = $col[0];
			$values[]         = $col[1];
			$field_values[$k] = $col;
		}
		$q   = "UPDATE " . $table . " SET ";
		$lim = "";
		foreach($fields as $k => $field)
		{
			$q .= $lim . $field . " = " . $placeholders[$k];
			$lim = ", ";
		}
		$q .= " WHERE ";
		$lim = "";
		foreach($where as $k => $col)
		{
			$q .= $lim . $k . " = %s";
			$lim = " AND ";
		}

		return $this->manipulateF($q, $types, $values);
	}

	/**
	 * @param string $table
	 * @return int
	 */
	public function nextId($table)
	{
		$this->db->beginTransaction();
		$res = $this->query("SELECT MAX(id) nextid FROM seq WHERE tablename = " . $this->quote($table));

		$id     = 1;
		$exists = false;
		while($row = $this->fetchAssoc($res))
		{
			$id     = $row['nextid'] + 1;
			$exists = true;
			break;
		}

		if($exists)
		{
			$this->manipulate("UPDATE seq SET id = id + 1 WHERE tablename = " . $this->quote($table));
		}
		else
		{
			$this->manipulate("INSERT INTO seq (id, tablename) VALUES(1, " . $this->quote($table) . ")");
		}
		$this->db->commit();

		return $id;
	}

	/**
	 * @param array $definition
	 */
	public function lockTables(array $definition)
	{
		$this->beginTransaction();
	}

	/**
	 *
	 */
	public function unlockTables()
	{
		$this->commit();
	}

	/**
	 *
	 */
	public function beginTransaction()
	{
		$this->db->beginTransaction();
	}

	/**
	 *
	 */
	public function commit()
	{
		$this->db->commit();
	}

	/**
	 *
	 */
	public function rollback()
	{
		$this->db->rollBack();
	}
}
