<?php

/**
* element
*
* class to manipulate elements, e.g. datasets
*
* @author Databay AG <info@databay.de>
* @package ilias-core
* @access public
* @version $Id$
*/
class Element {

    /**
    * database handle
    * @var object
    * @access private
    */
    var $dbHandle;

    /**
    * database table
    * @var string
    * @access private
    */
    var $dbTable;

    /**
    * associative array containing data of the element
    * @var array
    * @access private
    */
    var $data = array();

    /**
    * constructor
	* 
    * @param void
	* @return void
    */
    function Element() {
		// empty
    }

    /**
    * set data array
    * @see $data
    * @access public
	* @param array	$data	element data
	* @return void
    */
    function set($data) {
        // Has to be overwritten by object class
		//$this->data = $data;
    }

    /**
    * get data array
    * @see $data
    * @access public
	* @param void
	* @return void
    */
    function get() {
        // Has to be overwritten by object class	
        //return $this->data ;
    }

    /**
    * set database table
    * @param string database table
    * @see $dbTable
    * @access public
    */
    function setDbTable($AdbTable) {
        if ($AdbTable == "") {
            die("Element::setDbTable(): No database table given.");
        } else {
            $this->dbTable = $AdbTable;
        }
    }

    /**
    * get name of database table
    * @return string name of database table
    * @see $dbTable
    * @access public
    */
    function getDbTable() {
        return $this->dbTable;
    }

    /**
    * set database handle
    * @see $dbHandle
	* @access public
    * @param string database handle
	* @return void
    */
    function setDbHandle($AdbHandle) {
        if ($AdbHandle == "") {
			die("<b>Error: No Database handle given!</b><br>class: ".get_class($this)."<br>Script: ".__FILE__."<br>Line: ".__LINE__);
        } else {
            $this->dbHandle = $AdbHandle;
        }
    }

    /**
    * get database handle
    * @see $dbHandle
    * @access public
	* @param void
    * @return object database handle
    */
    function getDbHandle() {
        return $this->dbHandle;
    }

    /**
    * send a query to the database without getting a result
    * @param string $query query
    * @return bool true
    * @access public
    */
    function queryDb($query) {
        $this->checkDb("Element::queryDb()");
        //query is empty?
        if ($query == "") {
            die("Element::queryDb(): No query given.");
        }
        //evaluate the result
        $result = $this->dbHandle->query($query);
        if (DB::isError($result)) {
            die("Element::queryDb(): ".$result->getMessage());
        }

        return true;
    }

    /**
    * updates dataset where the unique database field matches a given value
    * uses the private data variable of this class for input
    * @param string $unique unique database field
    * @param mixed $value value for match
    * @access public
    */
    function updateDb($unique, $value) {
        $this->checkDb("Element::updateDb()");
        //check unique
        if ($unique == "") {
            die("Element::updateDB(): No unique database field given.");
        }
        //check value
        if ($value == "") {
            die("Element::updateDB(): No value given.");
        }
        //check the private data-array
        if (!is_array($this->data)) {
            die("Element::updateDB(): No data given.");
        }
        //build query
        $subq = "";
        while (list($key, $val) = each($this->data)) {
            if (substr($val, 0, 9) == "password(") {
                $val = trim($val);
                $val= substr($val, 10);
                $val = substr($val, 0, strlen($val) - 2);
                $subq .= $key. " = password(" . $this->dbHandle->quote($val . "") . "), ";
            } else {
                $subq .= $key . " = ". $this->dbHandle->quote($val) . ", ";
            }

        }
        /* alt
        while (list($key, $val) = each($this->data)) {
            $subq .= $key . " = " . $this->dbHandle->quote($val) . ", ";
        }
        */
        //query is empty
        if ($subq == "") {
            die("Element::updateDB(): No data given.");
        }
        //truncate subq (there is a comma...)
        $subq = substr($subq, 0, strlen($subq)-2);
        //set query
        $q = "UPDATE " . $this->dbTable . " SET " . $subq . " WHERE " . $unique . " = " . $this->dbHandle->quote($value);
        $this->dbHandle->query($q);
        //evaluate result
        if (DB::isError($result)) {
            die("Element::updateDb(): ".$result->getMessage()." : $q");
        }
    } //end function updateDb

    /**
    * inserts a new dataset
    * uses the private data variable of this class for input
    * the function examines $data and extracts the
    * keys and the values and builds the query
    * @access public
    */
    function insertDb() {
        $this->checkDb("Element::insertDb()");
        //check data
        if (!is_array($this->data)) {
            die("Element::insertDB(): No data given.");
        }
        //build query
        $fields = "";
        $values = "";
        while (list($key, $val) = each($this->data)) {
            $fields .= $key . ", ";
            if (substr($val, 0, 9) == "password(") {
                $val = trim($val);
                $val= substr($val, 10);
                $val = substr($val, 0, strlen($val) - 2);
                $values .= "password(" . $this->dbHandle->quote($val . "") . "), ";
            } else {
                $values .= $this->dbHandle->quote($val . "") . ", ";
            }
        }
        //check fields string
        if ($fields == "") {
            die("Element::insertDB(): No fields given.");
        }
        //check values-string
        if ($values == "") {
            die("Element::insertDB(): No values given.");
        }
        //truncate fields (there is a comma at the end...)
        $fields = substr($fields, 0, strlen($fields)-2);
        $values = substr($values, 0, strlen($values)-2);
        $q = "INSERT INTO " . $this->dbTable . " (" . $fields . ") VALUES (" . $values . ")";

        //evaluate result
        $result = $this->dbHandle->query($q);
        if (DB::isError($result)) {
            die("Element::insertDb(): ".$result->getMessage()." : $q");
        }
        //query the unique-key of inserted dataset
        $q = "SELECT LAST_INSERT_ID()";
        $this->result = $this->dbHandle->query($q);
        if (DB::isError($result)) {
            die("Element::insertDb()-Last_ID: ".$result->getMessage());
        }
        //query the result
        if ($data = $this->result->fetchRow()) {
            return $data[0];
        } else {
            return(0);
        }
    } //end function insertDb

    /**
    * get dataset from database where unique database field matches given value
    * @param string $unique unique database field
    * @param mixed $value value for match
    * @return array dataset
    * @access public
    */
    function getDbData($unique, $value) {
        $this->checkDb("Element::getDbData()");
        //check unique-key
        if ($unique == "") {
            die("Element::getDbData(): No unique database field given.");
        }
        //check value
        if ($value == "") {
            die("Element::getDbData(): No value given.");
        }
        //build query
        $q = "SELECT * FROM " . $this->getDbTable() . " WHERE " . $unique . " = " . $this->dbHandle->quote($value);
        $result = $this->dbHandle->query($q);
        //check result
        if (DB::isError($result)) {
            die("Element::getDbData(): ".$result->getMessage());
        }
        //return an associative array from query or false
        if ($result->numRows() > 0) {
            return $result->fetchRow(DB_FETCHMODE_ASSOC);
        } else {
            return false;
        }
    } //end function getDbData

    /**
    * get dataset from database using a given query
    * @param string $query query
    * @return array dataset
    * @access public
    */
    function getDbDataByQuery($query) {
        //check database handle
        if ($this->dbHandle == "") {
            die("Element::getDbDataByQuery(): No database handle given.");
        }
        //check query
        if ($query == "") {
            die("Element::getDbDataByQuery(): No query given.");
        }
        //send query
        $result = $this->dbHandle->query($query);
        //analyze resultset
        if (DB::isError($result)) {
            die("Element::getDbDataByQuery(): ".$result->getMessage());
        }
        //return associative array or false
        if ($result->numRows() > 0) {
            return $result->fetchRow(DB_FETCHMODE_ASSOC);
        } else {
            return false;
        }
    } //end function getDbDataByQuery

    /**
    * @param string
    * @param string
    */
    function getDbValueByQuery($query, $field) {
        //check database handle
        if ($this->dbHandle == "") {
            die("Element::getDbDataByQuery(): No database handle given.");
        }
        //check query
        if ($query == "") {
            die("Element::getDbDataByQuery(): No query given.");
        }
        $result = $this->dbHandle->query($query);
        //analyze resultset
        if (DB::isError($result)) {
            die("Element::getDbValueByQuery(): ".$result->getMessage());
        }
        //return associative array or false
        if ($result->numRows() > 0) {
            $R = $result->fetchRow(DB_FETCHMODE_ASSOC);
            return( $R[$field] );
        } else {
            return false;
        }
        
    }
    
} // end class Element

?>
