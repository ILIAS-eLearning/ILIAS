<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* element
*
* class to manipulate elements, e.g. datasets
*
* @author Databay AG <info@databay.de>
* @access public
* @version $Id$
*/
class ilElement {

    /**
    * database handle
    * @var object DB
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
    */
    function Element() {
		// empty
    }

    /**
    * set data array
    * @see $data
    * @access public
	* @param array	$data	element data
    */
    function set($data) {
        // Has to be overwritten by object class
		//$this->data = $data;
    }

    /**
    * get data array
    * @see $data
    * @access public
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
            die("ilElement::setDbTable(): No database table given.");
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
        $this->checkDb("ilElement::queryDb()");
        //query is empty?
        if ($query == "") {
            die("ilElement::queryDb(): No query given.");
        }
        //evaluate the result
        $result = $this->dbHandle->query($query);
        if (DB::isError($result)) {
            die("ilElement::queryDb(): ".$result->getMessage());
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
        $this->checkDb("ilElement::updateDb()");
        //check unique
        if ($unique == "") {
            die("ilElement::updateDB(): No unique database field given.");
        }
        //check value
        if ($value == "") {
            die("ilElement::updateDB(): No value given.");
        }
        //check the private data-array
        if (!is_array($this->data)) {
            die("ilElement::updateDB(): No data given.");
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
            die("ilElement::updateDB(): No data given.");
        }
        //truncate subq (there is a comma...)
        $subq = substr($subq, 0, strlen($subq)-2);
        //set query
        $q = "UPDATE " . $this->dbTable . " SET " . $subq . " WHERE " . $unique . " = " . $this->dbHandle->quote($value);
        $this->dbHandle->query($q);
        //evaluate result
        if (DB::isError($result)) {
            die("ilElement::updateDb(): ".$result->getMessage()." : $q");
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
        $this->checkDb("ilElement::insertDb()");
        //check data
        if (!is_array($this->data)) {
            die("ilElement::insertDB(): No data given.");
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
            die("ilElement::insertDB(): No fields given.");
        }
        //check values-string
        if ($values == "") {
            die("ilElement::insertDB(): No values given.");
        }
        //truncate fields (there is a comma at the end...)
        $fields = substr($fields, 0, strlen($fields)-2);
        $values = substr($values, 0, strlen($values)-2);
        $q = "INSERT INTO " . $this->dbTable . " (" . $fields . ") VALUES (" . $values . ")";

        //evaluate result
        $result = $this->dbHandle->query($q);
        if (DB::isError($result)) {
            die("ilElement::insertDb(): ".$result->getMessage()." : $q");
        }
        //query the unique-key of inserted dataset
        $q = "SELECT LAST_INSERT_ID()";
        $this->result = $this->dbHandle->query($q);
        if (DB::isError($result)) {
            die("ilElement::insertDb()-Last_ID: ".$result->getMessage());
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
        $this->checkDb("ilElement::getDbData()");
        //check unique-key
        if ($unique == "") {
            die("ilElement::getDbData(): No unique database field given.");
        }
        //check value
        if ($value == "") {
            die("ilElement::getDbData(): No value given.");
        }
        //build query
        $q = "SELECT * FROM " . $this->getDbTable() . " WHERE " . $unique . " = " . $this->dbHandle->quote($value);
        $result = $this->dbHandle->query($q);
        //check result
        if (DB::isError($result)) {
            die("ilElement::getDbData(): ".$result->getMessage());
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
            die("ilElement::getDbDataByQuery(): No database handle given.");
        }
        //check query
        if ($query == "") {
            die("ilElement::getDbDataByQuery(): No query given.");
        }
        //send query
        $result = $this->dbHandle->query($query);
        //analyze resultset
        if (DB::isError($result)) {
            die("ilElement::getDbDataByQuery(): ".$result->getMessage());
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
            die("ilElement::getDbDataByQuery(): No database handle given.");
        }
        //check query
        if ($query == "") {
            die("ilElement::getDbDataByQuery(): No query given.");
        }
        $result = $this->dbHandle->query($query);
        //analyze resultset
        if (DB::isError($result)) {
            die("ilElement::getDbValueByQuery(): ".$result->getMessage());
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
