<?php

/**
 * DB-Handler
 * The DB-Handler contains the SQL functions for the ILIAS3-Calendar.
 *
 * version 1.0
 * @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
 * @author MArtin Schumacher <ilias@auchich.de>
 * @author Mark Ulbrich <Mark_Ulbrich@web.de>
 **/

class ilCalDBHandler
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;

	/**
        * database table name
	* @var string
	* @access private
	*/

	var $dbTable;

	/**
	* class name
	* @var object
	* @access private
	*/

	var $className="ilCalDBHandler";

	/**
	* database table field for sorting the resultset
	* @var string
	* @access private
	*/

	var $orderConstruct;

	/**
	* field for result-limitation
	* @var string
	* @access private
	*/

	var $whereCondition;

	/**
	* table column
	* @var string
	* @access private
	*/

	var $column;

	/**
	* number of values, needed for the insert function
	* @var int
	* @access private
	*/

	var $number_of_values = 0;

	/**
	* value string, all values in one string
	* @var string
	* @access private
	*/

	var $values;

	/**
	* database query
	* @var string
	* @access private
	*/

	var $query;

	/**
	* result set
	* @var
	* @access private
	*/

	var $result;

	/**
	* DBHandler Constructor
	* @ access public
	*/
	function ilCalDBHandler() {
		global $ilias;
		$this->ilias =& $ilias;
	}


	/**
	* select statement
	* @returns result set
	* @param string $dbTable; opt: string $column, string $where, string orderConstruct
	* @access public
	*/
	function select($dbTable ,$column="" ,$whereCondition="" ,$orderConstruct="") {
		if (strlen($dbTable) == 0) {
			die($this->className . "::select(): No dbTable given.");
		}
		else {
			if (strlen($column) == 0) {
				$column = "*";
			}
			if (strlen($orderConstruct) == 0) {
		   	if (strlen($whereCondition) == 0) {
			     	$query = "SELECT {$column} FROM {$dbTable}";
		      }
		      else {
			     	$query = "SELECT {$column} FROM {$dbTable} WHERE {$whereCondition}";
			   }
		   }
		   else {
		   	if (strlen($whereCondition) == 0) {
		      	$query = "SELECT {$column} FROM {$dbTable} ORDER BY {$orderConstruct}";
		      }
		      else {
		      	$query = "SELECT {$column} FROM {$dbTable} WHERE {$whereCondition} ORDER BY {$orderConstruct}";
				}
		   }
			$result = $this->ilias->db->query($query);
			//echo "Select: ".$query."<br>";
			return $result;
		}
	}

	/**
	* insert statement
	* @param int $number_of_values, string $values (only a single string), string $dbTable
	* @access public
	*/
	function insert($number_of_values, $dbTable, $fields, $values) {
      switch ($number_of_values) {
      	case 0: die($this->className . "::insert(): no number_of_values given.");
                 break;
         case 1: $query = "INSERT INTO {$dbTable} ({$fields}) VALUE ({$values})";
                 break;
         default: $query = "INSERT INTO {$dbTable} ({$fields}) VALUES ({$values})";
      }
	   //echo "<b>SQL query:</b> ".$query."<br>";
		$result = $this->ilias->db->query($query);
	}

	/**
	* update statement
	* @param string dbTable, string values (<column>=<new value>), string whereCondition
	* @access public
	*/
	
	function update($dbTable, $values, $whereCondition) {
		if (strlen($dbTable) != 0 && strlen($values) != 0 && strlen($whereCondition) != 0) {
			$query = "UPDATE {$dbTable} SET {$values} WHERE {$whereCondition}";
			$result = $this->ilias->db->query($query);
		}
		else {
			die ($this->className . "::update(): incorrect parameters.");
		}
	}

	/**
	* delete statement
	* @param string dbTable, string whereCondition
	* @access public
	*/
	
	function delete($dbTable, $whereCondition, $delete=false) {
		if (strlen($dbTable) != 0 && strlen($whereCondition) != 0) {
			$query = "DELETE FROM {$dbTable} WHERE {$whereCondition}";
			$result = $this->ilias->db->query($query);
		}
		elseif (strlen($dbTable) != 0 && strlen($whereCondition) == 0 && $delete == true) {
			$query = "DELETE FROM {$dbTable}";
			$result = $this->ilias->db->query($query);
		}
		else {
			die ($this->className . "::delete(): incorrect parameters.");
		}
	}

}

?>
