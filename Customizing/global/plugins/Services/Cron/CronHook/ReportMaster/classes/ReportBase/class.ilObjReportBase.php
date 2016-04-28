<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportTable.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportOrder.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportQuery.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportQueryOn.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilter.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettingsDataHandler.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingFactory.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/class.ilReportMasterPlugin.php");
/**
* This class performs all interactions with the database in order to get report-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
abstract class ilObjReportBase extends ilObjectPlugin {
	protected $online;
	protected $gIldb;
	protected $gTree;

	protected $filter = null;
	protected $filter_action = null;
	protected $query = null;
	protected $table = null;
	protected $order = null;
	protected $user_utils;

	public $sf;
	public $master_plugin;
	public $settings;

	const HTTP_REGEX = "/^(https:\/\/)|(http:\/\/)/";

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
		global $ilDB, $ilUser, $tree;
		$this->user_utils = gevUserUtils::getInstanceByObj($ilUser);
		$this->gIldb = $ilDB;
		$this->gTree = $tree;
		$this->table = null;
		$this->query = null;
		$this->data = false;
		$this->filter = null;
		$this->order = null;

		$this->s_f = new settingFactory($this->gIldb);
		$this->master_plugin = new ilReportMasterPlugin();
		$this->settings = array();
		$this->createLocalReportSettings();
		$this->createGlobalReportSettings();
		$this->settings_data_handler = $this->s_f->reportSettingsDataHandler();

	}


	abstract protected function createLocalReportSettings();

	protected function createGlobalReportSettings() {

		$this->global_report_settings =
			$this->s_f->reportSettings('rep_master_data')
				->addSetting($this->s_f
								->settingBool('is_online', $this->master_plugin->txt('is_online'))
								)
				->addSetting($this->s_f
								->settingString('pdf_link', $this->master_plugin->txt('rep_pdf_desc'))
									->setFromForm(function ($string) {
										$string = trim($string);
										if($string === "" || preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1 ) {
											return $string;
										}
										return 'https://'.$string;
									})
								)
				->addSetting($this->s_f
								->settingString('video_link', $this->master_plugin->txt('rep_video_desc'))
									->setFromForm(function ($string) {
										$string = trim($string);
										if($string === "" || preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1 ) {
											return $string;
										}
										return 'https://'.$string;
									})
								)
				->addSetting($this->s_f
								->settingRichText('tooltip_info', $this->master_plugin->txt('rep_tooltip_desc'))
								);
	}

	public function getSettingsData() {
		return $this->settings;
	}

	public function setSettingsData(array $settings) {
		$this->settings = $settings;
	}

	public function prepareReport() {
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
	abstract protected function buildOrder($order);
	
	protected function buildTable($table) {
		return $table	->template($this->getRowTemplateTitle(), $this->plugin->getDirectory());
	}

	/**
	* The sql-query is built by the following methods.
	*/
	protected function queryWhere() {
		$query_part = $this->query ? $this->query->getSqlWhere() : ' TRUE ';
		$filter_part = $this->filter ? $this->filter->getSQLWhere() : ' TRUE ';
		return ' WHERE '.$filter_part.' AND '.$query_part;
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

	public function buildQueryStatement() {
		return $this->query->sql()."\n "
			   . $this->queryWhere()."\n "
			   . $this->query->sqlGroupBy()."\n"
			   . $this->queryHaving()."\n"
			   . $this->queryOrder();
	}

	/**
	* this stores query results to an array
	*/
	protected function fetchData(callable $callback) {
		if ($this->query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		
		$query = $this->buildQueryStatement();
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

	abstract protected function getRowTemplateTitle();


	final public function doCreate() {
		$this->settings_data_handler->createObjEntry($this->getId(), $this->global_report_settings);
		$this->settings_data_handler->createObjEntry($this->getId(), $this->local_report_settings);
	}

	final public function doRead() {
		$this->settings = array_merge($this->settings_data_handler->readObjEntry($this->getId(), $this->global_report_settings),
							$this->settings_data_handler->readObjEntry($this->getId(), $this->local_report_settings));
	}

	final public function doUpdate() {
		$this->settings_data_handler->updateObjEntry($this->getId(), $this->global_report_settings,$this->settings);
		$this->settings_data_handler->updateObjEntry($this->getId(), $this->local_report_settings,$this->settings);
	}

	final public function doDelete() {
		$this->settings_data_handler->deleteObjEntry($this->getId(), $this->global_report_settings);
		$this->settings_data_handler->deleteObjEntry($this->getId(), $this->local_report_settings);
	}

	final public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$this->settings_data_handler->cloneObj($this->getId(), $this->global_report_settings, $new_obj);
		$this->settings_data_handler->cloneObj($this->getId(), $this->local_report_settings, $new_obj);
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

	/**
	* We may need to locate the report inside the tree, so it is possible to perform local evaluations.
	* look for the first parent object of specific @param (string)type,
	* or the first parent object, if no type given. @return array(obj_id => id, ref_id => id).
	*/
	protected function getParentObjectOfTypeIds($type = null) {
		$data = $this->gTree->getParentNodeData($this->getRefId());
		while( null !== $type && $type !== $data['type'] && (string)ROOT_FOLDER_ID !== (string)$data['ref_id'] ) {
			$data = $this->gTree->getParentNodeData($data['ref_id']);
		}
		return (null === $type || $type === $data['type'] )
			? array('obj_id' => $data['obj_id'], 'ref_id' => $data['ref_id']) : array();
	}

	/**
	* It seems to be a common problem to ev2aluate certain types in a subtree.
	*/
	protected function getSubtreeTypeIdsBelowParentType($subtree_type,$parent_type) {
		$parent_cat_ref_id = $this->getParentObjectOfTypeIds($parent_type)['ref_id'];
		if($parent_cat_ref_id === null) {
			return array();
		}
		$subtree_nodes_data = $this->gTree->getSubTree(
			$this->gTree->getNodeData($parent_cat_ref_id),true, $subtree_type);
		$return = array();
		foreach ($subtree_nodes_data as $node) {
			$return[] = $node["obj_id"];
		}
		return $return;
	}


}