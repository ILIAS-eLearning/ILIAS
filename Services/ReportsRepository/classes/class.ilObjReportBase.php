<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Services/ReportsRepository/classes/class.catReportOrder.php';
require_once 'Services/ReportsRepository/classes/class.catReportQuery.php';
require_once 'Services/ReportsRepository/classes/class.catReportQueryOn.php';
require_once 'Services/ReportsRepository/classes/class.catFilter.php';
require_once 'Services/ReportsRepository/classes/class.catReportTable.php';
/**
* This class performs all interactions with the database in order to get report-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
abstract class ilObjReportBase extends ilObjectPlugin {
	protected $online;
	protected $gIldb;

	protected $filter = null;
	protected $query = null;
	protected $table = null;
	protected $order = null;

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
		global $ilDB;
		$this->gIldb = $ilDB;
		$this->table = null;
		$this->query = null;
		$this->data = false;
		$this->filter = null;
		$this->order = null;
	}


	final public function prepareReport() {
		$this->filter = $this->buildFilter(catFilter::create());

		$this->table = $this->buildTable(catReportTable::create());
		$this->query = $this->buildQuery(catReportQuery::create());
		$this->order = $this->buildOrder(catReportOrder::create($this->table));
		$this->buildRelevantParameters();
	}

	public function deliverFilter() {
		return $this->filter;
	}
	public function deliverTable() {
		if($this->table !== null ) {
			return $this->table;
		}
		throw new Exception("cilObjReportBase::deliverTable: you need to define a table.");
	}
	public function deliverOrder() {
		return $this->order;
	}

	abstract protected function buildQuery($query);
	abstract protected function buildFilter($filter);
	abstract protected function buildTable($table);
	abstract protected function buildOrder($order);
	abstract protected function buildRelevantParameters();
	
	/**
	* The sql-query is built by the following methods.
	*/
	protected function queryWhere() {
		if ($this->filter === null) {
			return " WHERE TRUE";
		}
		
		return " WHERE ".$this->filter->getSQLWhere();
	}
	
	protected function queryHaving() {
		if ($this->filter === null) {
			return "";
		}
		$having = $this->filter->getSQLHaving();
		if (trim($having) === "") {
			return "";
		}
		return " HAVING ".$having;
	}
	
	protected function queryOrder() {
		if ($this->order === null ||
			in_array($this->order->getOrderField(), 
				$this->internal_sorting_fields ? $this->internal_sorting_fields : array())
			) {
			return "";
		}
		return $this->order->getSQL();
	}

	protected function groupData($data) {
		$grouped = array();
		
		foreach ($data as $row) {
			$group_key = $this->makeGroupKey($row);
			if (!array_key_exists($group_key, $grouped)) {
				$grouped[$group_key] = array();
			}
			$grouped[$group_key][] = $row;
		}

		return $grouped;
	}

	protected function makeGroupKey($row) {
		$head = "";
		$tail = "";
		foreach ($this->table->_group_by as $key => $value) {
			$head .= strlen($row[$key])."-";
			$tail .= $row[$key];
		}
		return $head.$tail;
	}

    /**
    * The following methods perform the query and collect data. 
    * getData returns the results, to be put into the table.
    */
	public function deliverData(callable $callback) {  
		if ($this->data == false){
			$this->data = $this->fetchData($callback);
		}
		return $this->data;
	}

	public function deliverGroupedData(callable $callback) {
		return $this->groupData($this->deliverData($callback));
	}

	/**
	* this stores query results to an array
	*/
	protected function fetchData(callable $callback) {
		if ($this->query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		
		$query = $this->query->sql()."\n "
			   . $this->queryWhere()."\n "
			   . $this->query->sqlGroupBy()."\n"
			   . $this->queryHaving()."\n"
			   . $this->queryOrder();
			   //die($query);

		
		$res = $this->gIldb->query($query);
		$data = array();
		
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
		}

		return $data;
	}
	public function getRelevantaParameters() {
		return $this->relevant_parameters;
	}
}