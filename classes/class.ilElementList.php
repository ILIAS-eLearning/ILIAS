<?PHP

require_once dirname(__FILE__) . "/class.ilElement.php";

/**
* list of elements
*
* get lists of elements, e.g. datasets from a database
*
* @author   Databay AG <info@databay.de>
* @access   public
* @version  $Id$
*/
class ilElementList {

    /**
    * database handle
    * @var object DB
    * @see setDbHandle(), getDbHandle()
    * @access private
    */
    var $dbHandle;

    /**
    * result handle (from query)
    * @var object DB
    * @access private
    */
	var $result;
	
    /**
    * database table name
    * @var string
    * @see setDbTable(), getDbTable()
    * @access private
    */
    var $dbTable;

    /**
    * unique database table field
    * @var string
    * @see setIdField()
    * @access private
    */
    var $idField="id";

    /**
    * database table field for sorting the results
    * @var string
    * @see setOrderField()
    * @access private
    */
    var $orderField="id";

    /**
    * sorting direction for results: ASC or DESC
    * @var string
    * @see setOrderDirection()
    * @access private
    */
    var $orderDirection="ASC";

    /**
    * where condition for result
    * @var string
    * @see setWhereCond()
    * @access private
    */
    var $whereCond="1";


    /**
    * list element
    * @var object Element
    * @access public
    */
    var $element;

    function ilElementList() {

	    $this->idField="id";
	    $this->orderField="id";
		$this->orderDirection="ASC";
		$this->whereCond="1";

        $this->element = new ilElement();
    }

    /**
    * check whether database handle and table are set
    * dies if the database handle or the database table aren't set
    * @param string $function name of function that called this function
    * @access private
    */
    function checkDb($function) {
        if ($this->dbHandle == "") {
            die($function . ": No database handle given.");
        }
        if ($this->dbTable == "") {
            die($function . ": No database table given.");
        }
    }

    /**
    * set database table
    * @param string $dbTable database table
    * @see $dbTable
    * @access public
    */
    function setDbTable($dbTable) {
        if ($dbTable == "") {
            die ("List::setDbTable(): No database table given.");
        } else {
            $this->dbTable = $dbTable;
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
    * @param string $dbHandle database handle
    * @see $dbHandle
    * @access public
    */
    function setDbHandle($dbHandle) {
        if ($dbHandle == "") {
            die("Liste::setDbHandle(): No database handle given.");
        } else {
            $this->dbHandle = $dbHandle;
        }
    }

    /**
    * get database handle
    * @return object database handle
    * @see $dbHandle
    * @access public
    */
    function getDbHandle() {
        return $this->dbHandle;
    }

    /**
    * set unique database field
    * @param string $idField unique database field
    * @see $idField
    * @access private
    */
    function setIdField($idField) {
        if ($idField == "") {
            die ("Liste::setIdField(): No id given.");
        } else {
            $this->idField = $idField;
        }
    }

    /**
    * set where condition for result fields
    * @param string $where condition for result fields
    * @see $whereCond
    * @access private
    */
    function setWhereCond($where) {
        if ($where == "") {
            die ("Liste::setWhereCond(): No where condition given.");
        } else {
            $this->whereCond = $where;
        }
    }

	
    /**
    * set database field for sorting results
    * @param string $orderField database field for sorting
    * @see $orderField
    * @access private
    */
    function setOrderField($orderField) {
        if ($orderField == "") {
            die ("Liste::setOrderField(): No order field given.");
        } else {
            $this->orderField = $orderField;
        }
    }

    /**
    * set sorting direction for results: ASC or DESC
    * @param string $orderDirection sorting direction
    * @return boolean false if parameter is whether ASC nor DESC
    * @see $orderDirection
    * @access private
    */
    function setOrderDirection($orderDirection) {
        if (($orderDirection != "ASC") && ($orderDirection != "DESC")) {
            return false;
        } else {
            $this->orderDirection = $orderDirection;
        }
    }

    /**
    * select all datasets of one database table without any limitations
    * @return object result result identifier for use with getDbNextElement()
    * @access public
    * @see checkDb(), selectDbAllLimited(), getDbNextElement()
    */
    function selectDbAll()
	{
        $this->checkDb("Liste::selectDbAll()");
		
#		echo "SELECT * FROM " . $this->getDbTable() . " WHERE (".$this->whereCond.") ORDER BY " . $this->orderField . " " . $this->orderDirection;
        $result = $this->dbHandle->query("SELECT * FROM " . $this->getDbTable() . " WHERE (".$this->whereCond.") ORDER BY " . $this->orderField . " " . $this->orderDirection);
        if (DB::isError($result)) {
            die("Liste::selectDbAll(): ".$result->getMessage());
        }
		$this->result=$result;
        return $result;
    }

    /**
    * select a limited number of datasets of one database table
    * @param integer $start first datasets to be selected
    * @param integer $count max. number of datasets to be selected
    * @return object database result identifier for use with getDbNextElement()
    * @access public
    * @see selectDbAll(), getDbNextElement()
    */
    function selectDbAllLimited($start = 0, $count = 30) {
        $this->checkDb("Liste::selectDbQuery()");

        $result = $this->dbHandle->query("SELECT * FROM " . $this->getDbTable() . " WHERE (".$this->whereCond.") ORDER BY " . $this->orderField . " " . $this->orderDirection . " LIMIT " . $start . ", " . $count);
        if (DB::isError($result)) {
            die("Liste::selectDbAllLimited(): ".$result->getMessage());
        }
		$this->result=$result;
        return $result;
    }

    /**
    * select datasets by query
    * @param string $query select statement
    * @return object result identifier for use with getDbNextElement()
    * @access public
    */
    function selectDbAllByQuery($query) {
        if ($this->dbHandle == "") {
            die("Liste::selectDbAllByQuery(): No database handle given.");
        }
        if ($query == "") {
            die("Liste::selectDbAllByQuery(): No query given.");
        }

        $result = $this->dbHandle->query($query);
        if (DB::isError($result)) {
            die("Liste::selectDbAllByQuery(): ".$result->getMessage());
        }
		$this->result=$result;
        return $result;
    }

    /**
    * get next dataset of a (optionally) given select result
    * @param string result identifier returned by functions like selectDbAll(), selectDbAllLimited(), selectDbAllByQuery()
    * @return boolean false if no further result datasets are given, otherwise true
    * @access public
    * @see selectDbAll(), selectDbAllLimited(), selectDbAllByQuery()
    */
    function getDbNextElement($result="default") {
        //check result
		if ($result=="default") {
		    $result=$this->result;
		}
		
        if (!is_object($result)) {
            die("Liste::getDbNextElement(): No result object given.");
        }
        //get the next dataset
        if (is_array($data = $result->fetchRow(DB_FETCHMODE_ASSOC))) {
            $this->element->setData($data);
            return true;
        } else {
            return false;
        }
    }

    /**
    * number of found datasets for a count query
    * @param string $where limiting where statement
    * @return int number of found datasets
    * @access public
    * @see countDbByQuery()
    */
    function countDb($where = "") {
        $this->checkDb("Liste::countDb()");

        $q = "SELECT COUNT(*) FROM " . $this->getDbTable();
        if ($where != "") {
            $q .= " WHERE " . $where;
			} else {
			$q .= " WHERE (" . $this->whereCond . ")";
			}
        $result = $this->dbHandle->query($q);
        if (DB::isError($result)) {
            die("Liste::countDb(): ".$result->getMessage());
        }
        if (is_array($data = $result->fetchRow())) {
            return $data[0];
        } else {
            return 0;
        }
    } //end function countDb

    /**
    * number of found datasets for a count query
    * @param string $query count query (e.g. SELECT COUNT(*) FROM TABLE)
    * @return int number of found datasets
    * @access public
    * @see countDb()
    */
    function countDbByQuery($query) {
        if ($this->dbHandle == "") {
            die("Liste::countDbByQuery(): No database handle given.");
        }
        if ($query == "") {
            die("Liste::countDbByQuery(): No query given.");
        }

        $result = $this->dbHandle->query($query);
        if (DB::isError($result)) {
            die("Liste::countDbByQuery(): ".$result->getMessage());
        }
        if (is_array($data = $result->fetchRow())) {
            return $data[0];
        } else {
            return 0;
        }
    } //end function countDbByQuery

} //end class ElementList

?>
