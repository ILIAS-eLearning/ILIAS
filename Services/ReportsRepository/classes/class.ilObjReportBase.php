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
	protected $filter_action = null;
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
		$this->addFilterToRelevantParameters();
	}

	public function addRelevantParameter($key, $value) {
		$this->relevant_parameters[$key] = $value;
	}

	protected function addFilterToRelevantParameters() {
		if($this->filter) {
			$this->addRelevantParameter($this->filter->getGETName(),$this->filter->encodeSearchParamsForGET());
		}
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

	public function setFilterAction($link) {
		$this->filter_action = $link;
	}

	public function getRelevantaParameters() {
		return $this->relevant_parameters;
	}

	// Report discovery

	/**
	 * Get a list with object data (obj_id, title, type, description, icon_small) of all
	 * Report Objects in the system that are not in the trash. The id is
	 * the obj_id, not the ref_id.
	 *
	 * @return array
	 */
	static public function getReportsObjectData() {
		require_once("Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

		global $ilPluginAdmin;

		$c_type = ilRepositoryObjectPlugin::getComponentType();
		$c_name = ilRepositoryObjectPlugin::getComponentName();
		$slot_id = ilRepositoryObjectPlugin::getSlotId();
		$plugin_names = $ilPluginAdmin->getActivePluginsForSlot($c_type, $c_name, $slot_id);

		$obj_data = array();

		foreach ($plugin_names as $plugin_name) {
			$plugin = $ilPluginAdmin->getPluginObject($c_type, $c_name, $slot_id, $plugin_name);
			assert($plugin instanceof ilRepositoryObjectPlugin);

			if (!($plugin instanceof ilReportBasePlugin)) {
				continue;
			}

			// this actually is the object type
			$type = $plugin->getId();

			$icon = ilRepositoryObjectPlugin::_getIcon($type, "small");

			$obj_data[] = array_map(function(&$data) use (&$icon) {
					// adjust data to fit the documentation.
					$data["obj_id"] = $data["id"];
					unset($data["id"]);
					$data["icon"] = $icon;
					return $data;
											// second parameter is $a_omit_trash
				}, ilObject::_getObjectsDataForType($type, true));
		}

		return call_user_func_array("array_merge", $obj_data);
	}

	/**
	 * Get a list of all reports visible to the given user. Returns a list with entries
	 * title.obj_id => (obj_id, title, type, description, icon). If a report is visible
	 * via two different ref_ids only one of those will appear in the result.
	 *
	 * @param	ilObjUser $user
	 * @return	array
	 */
	static public function getVisibleReportsObjectData(ilObjUser $user) {
		require_once("Services/Object/classes/class.ilObject.php");

		global $ilAccess;

		$reports = self::getReportsObjectData();

		$visible_reports = array();

		foreach ($reports as $key => &$report) {
			$obj_id = $report["obj_id"];
			$type = $report["type"];
			foreach (ilObject::_getAllReferences($report["obj_id"]) as $ref_id) {
				if ($ilAccess->checkAccessOfUser($user->getId(), "read", null, $ref_id)) {//, $type, $obj_id)) {
					$report["ref_id"] = $ref_id;
					$visible_reports[$key] = $report;
					break;
				}
			}
		}

		ksort($visible_reports, SORT_NATURAL | SORT_FLAG_CASE);
		return $visible_reports;
	}
}