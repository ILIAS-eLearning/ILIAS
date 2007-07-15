<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 * 
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1 
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *  
 * Database class for demonstration of current state of ILIAS SCORM 2004
 * Typical usage is static for main DB in your project.
 * Only to connect to other related databases (e.g. external user login 
 * store) you have to create instances of ilSCORM13DB.
 * Static use starts width ilSCORM13DB::init(PDODSN);
 * Instance use starts width $db = new ilSCORM13DB(PDODSN);
 */ 


class ilSCORM13DB
{
	// variables for static usage 
	public static $DB;
	private static $DSN;
	private static $TYPE;
	private static $BRACKETS;
	private static $LAST_ID;
	private static $ERRORS;
	private static $BRACKETS_LIST = array(
		'mysql' => '``', 
		'jet'   => '[]',
		'sqlite' => '""',
	); // for table or field names containing whitespace or other special chars

	// similar variables for dynamic usage 
	private $db;
	private $dsn;
	private $type;
	private $brackets;
	private $lastId;
	private static $errors;
	
	private static $SQLCOMMAND = array();

	public function __construct($dsn, $type='mysql') 
	{
		$this->db = new PDO($dsn); 
		$this->dsn = $dsn; 
		$this->brackets = self::$BRACKETS_LIST[$type]; 
		$this->type = is_null($type) ? substr($dsn, 0, strpos($dsn, ':')) : $type;
		$this->brackets = self::$BRACKETS_LIST[$this->type]; 
	}
	
	public function init($dsn, $type='mysql') 
	{	
		self::$DB = new PDO($dsn); 
		self::$DSN = $dsn;
		self::$TYPE = is_null($type) ? substr($dsn, 0, strpos($dsn, ':')) : $type;
		self::$BRACKETS = self::$BRACKETS_LIST[self::$TYPE]; 
	}
	
	public function addQueries($name) 
	{
		require_once($name . '_' . self::getType() . '.php');
	}
	
	public function getLastId() 
	{
		return self::getDB()->lastInsertId();
		return $this && $this instanceof ilSCORM13DB
			? $this->lastId
			: self::$LAST_ID; 
	}
	
	public function getType() 
	{
		return $this && $this instanceof ilSCORM13DB
			? $this->type
			: self::$TYPE; 
	}
	
	private function getDSN() 
	{
		return $this && $this instanceof ilSCORM13DB 
			? $this->dsn 
			: self::$DSN; 
	}
	
	private function getDB() 
	{
		return $this && $this instanceof ilSCORM13DB 
			? $this->db
			: self::$DB; 
	}
	
	private function escapeName($name) 
	{
		$b = $this && $this instanceof ilSCORM13DB 
			? $this->brackets 
			: self::$BRACKETS; 
		return $b[0] . preg_replace('/[^\w_.-]/', '_', $name) . $b[1];
	}
	
	private function setLastId($id) 
	{
		$this && $this instanceof ilSCORM13DB
			? $this->lastId = $id
			: self::$LAST_ID = $id;
	}
	
	public function & getRecord($tableOrView, $idname, $idvalue) 
	{
		if (!is_string($idname) || !is_numeric($idvalue))
		{
			return false;
		}
		$q = 'SELECT * FROM ' . self::escapeName($tableOrView) . ' WHERE ' . self::escapeName($idname) . '=' . $idvalue;
		$r = self::query($q);
		return $r[0];
	}
	
	public function setRecord($tableOrView, $row, $idname=null) 
	{
		$r = self::setRecords($tableOrView, array($row), $idname);
		return $r[0];
	}
	
	public function & getRecords($tableOrView, $idname=null, $idvalues=null, $order=null, $paging=null) 
	{
		$tableOrView = self::escapeName($tableOrView);
		$q = "SELECT * FROM $tableOrView";
		if (is_string($idname) && is_array($idvalues))
		{
			$idname = self::escapeName($idname);		
			foreach ($idvalues as &$idvalue) 
			{
				if (!is_numeric($idvalue)) return false;
				$idvalue = "$idname=$idvalue";
			}
			$q .= ' ' . implode(' OR ', $idvalues);
		}
		return self::query($q, null, $order, $paging);
	}
	
	function setRecords($tableOrView, $rows, $idname=null)
	{
		//$d = new PDO(self::getDSN());
		$d = self::getDB();
		$r = 0;
		if (!is_array($row = $rows[0]))
		{
			return false;
		}
		$tableOrView = self::escapeName($tableOrView);
		$q = array();
		if (is_string($idname)) {
			$idvalue = $row[$idname];
			$idname = self::escapeName($idname);
		}
		$u = is_numeric($idvalue);
		if ($u)
		{
			foreach (array_keys($row) as $k) 
			{
				$q[] = self::escapeName($k) . '=?';
			}
			$q = implode(', ', $q);
			$q = "UPDATE $tableOrView SET $q WHERE $idname=$idvalue";
		}
		else 
		{			
			foreach (array_keys($row) as $k) 
			{
				$q[] = self::escapeName($k);
			}
			$q = implode(', ', $q);
			$q = "INSERT INTO $tableOrView ($q) VALUES (" . str_pad('', count($row)*2-1, '?,') . ')';
		}
		//echo "<br>$q";
		if ($s = $d->prepare($q)) 
		{
			$type = self::getType();
			foreach ($rows as &$row)
			{
				$row = $s->execute(array_values($row));
				if (!$u && is_string($idname) && $row)
				{
					if ($type==='jet') {
						$row = self::query("SELECT Max($idname) AS lastInsertId FROM $tableOrView");
						$row = $row[0]['lastInsertId']+1;
					} else {
						$row = $d->lastInsertId();
					}
					self::setLastId($row);
				} 
			} 
		}
		unset($d);
		return $rows;
	}

	public function removeRecord($table, $idname, $idvalue) 
	{
		self::removeRecords($table, $idname, array($idvalue));
	}
	
	public function removeRecords($tables, $idnames, $idvalues) 
	{
		if (!is_array($idvalues))
		{
			return false;
		}
		//$d = new PDO(self::getDSN());
		$d = self::getDB();
		if (!is_array($tables))
		{
			$tables = array($tables);
		} 
		if (!is_array($idnames))
		{
			$idnames = array($idnames);
		}
		$tables = array_reverse($tables);
		foreach ($tables as $i => &$table) 
		{
			$table = self::escapeName($table);
			$idname = $idnames[$i % count($idnames)];
			if (!is_string($idname)) return false;
			$idname = self::escapeName($idname);
			$q = "DELETE FROM $table WHERE $idname=?";
			foreach ($idvalues as $idvalue) 
			{
				$table = self::exec($q, $idvalue);
			}
		}
		unset($d);
		return array_reverse($tables);
	}	
	
	public function & query($query, $params=null, $order=null, $paging=null, $fetchType=PDO::FETCH_ASSOC) 
	{
		$r = array();
		//$d = new PDO(self::getDSN());
		$d = self::getDB();
		$q = array(self::$SQLCOMMAND[$query] ? self::$SQLCOMMAND[$query] : $query);
		if (is_array($order))
		{
			$o = array();
			foreach ($order as $k => $v) 
			{
				$o[] = self::escapeName($k) . ' ' . ($v ? 'ASC' : 'DESC');
			}  
			$q[] = 'ORDER BY ' . implode(', ', $o);
		}
		if (is_array($paging))
		{
			if (is_int($o = $paging['count'])) 
			{
				// MySQL Style
				$q[] = "LIMIT $o"; 
				if (is_int($o = $paging['offset'])) 
				{
					$q[] = "OFFSET $o";
				}
			}
		}
		$q = implode(' ', $q);
		$s = $d->prepare($q);
		$s->execute($params);
		$r = $s->fetchAll($fetchType);
		unset($d);
		return $r;
	}
		
	/**
	 * exec('delete...') 	
	 * exec('delete... id=?', array(231)) 	
	 * exec('insert... id=?', array(array(231), array(130))) 	
	 * exec(array('select... id=?', 'select2... id=?'), array(array(231))) 	
	 * exec(array('select... id=?', 'select2... id=?'), array(array(231), array(130)))
	 * 
	 * erstelle gleich dimensionale arrays f�r $queries und $params
	 */	
	 public function exec($queries, $params=null, &$result = null) 
    { 
        if (!is_array($queries)) 
        { 
            $r = self::exec(array($queries), $params); 
            return $r[0]; 
        } 
        if (!is_array($params))  
        { 
            $params = array(); 
        } 
        if (!is_array(current($params)))  
        { 
            $params = array($params); 
        } 
        //$d = new PDO(self::getDSN()); 
        $d = self::getDB(); 
        foreach ($queries as $i => &$q)  
        { 
           if ($s = $d->prepare($sql = (self::$SQLCOMMAND[$q] ? self::$SQLCOMMAND[$q] : $q)))  
            { 
                $q = 0; 
                $r = array(); 
                $ps = is_array($params) ? $params[$i % count($params)] : null; 
                if (!is_array(current($ps)))  
                { 
                    $ps = array($ps); 
                } 
                foreach ($ps as $p)  
                { 
                 $q+=$s->execute($p);
                 $arr = $s->errorInfo();
                 file_put_contents('sql.log', implode("\n", array('', date('c'), $sql, var_export($p, true),var_export($arr,true))), FILE_APPEND);  
                
                    if (is_array($result))  
                    { 
                        count($queries)<2 
                            ? $result = $s->fetchAll(PDO::FETCH_ASSOC) 
                            : $result[] = $s->fetchAll(PDO::FETCH_ASSOC); 
                    } 
                } 
            } 
            else 
            { 
                // prepare failed 
            } 
        } 
        unset($d); 
        return $queries;  
    } 
	

	function begin()
	{
		self::getDB()->beginTransaction();
		self::$ERRORS = 0;
	}
		
	function commit()
	{
		self::$ERRORS 
			? self::getDB()->rollBack() 
			: self::getDB()->commit();
		return self::$ERRORS;
	}
		
	function rollback()
	{
		self::getDB()->rollBack(); 
	}
		
}

?>
