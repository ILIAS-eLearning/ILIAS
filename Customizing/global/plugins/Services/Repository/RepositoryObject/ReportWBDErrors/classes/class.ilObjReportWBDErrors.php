<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportWBDErrors extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $gCtrl;

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		global $ilCtrl,$lng;
		$this->gCtrl = $ilCtrl;
		$this->gLng = $lng;
	}

	public function initType() {
		$this->setType("xwbe");
	}
	
	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_wbe');
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_wbd_errors_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildOrder($order) {
		$order 	->mapping("course_id", "err.crs_id")
				->mapping("resolve", "err.ts")
				->defaultOrder("ts", "DESC");
		return $order;
	}

	protected function buildTable($table) {
		$table	->column("ts", $this->plugin->txt("ts"), true)
				->column("action", $this->plugin->txt("wbd_errors_action"), true)
				->column("internal", $this->plugin->txt( "wbd_errors_internal"), true)
				->column("user_id", $this->plugin->txt("usr_id"), true)
				->column("course_id", $this->plugin->txt("crs_id"), true)
				->column("firstname", $this->plugin->txt("firstname"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("title", $this->plugin->txt("title"), true)
				->column("begin_date", $this->plugin->txt("begin_date"), true)
				->column("end_date", $this->plugin->txt("end_date"), true)
				->column("reason",$this->plugin->txt( "wbd_errors_reason"), true)
				->column("reason_full", $this->plugin->txt("wbd_errors_reason_full"), true)
				->column("resolve", $this->plugin->txt("wbd_errors_resolve"), 1, 0, 1);
		return parent::buildTable($table);
	}

	protected function buildQuery($query) {
		$query	->distinct()
				->select("err.id")
				->select("err.usr_id")
				->select("err.crs_id")
				->select("err.internal")
				->select("err.reason")
				->select("err.reason_full")
				->select("err.ts")
				->select("err.action")
				->select("ud.firstname")
				->select("ud.lastname")
				->select("crs.title")
				->select("usrcrs.begin_date")
				->select("usrcrs.end_date")
				->from("wbd_errors err")
				->left_join("hist_user usr")
					->on("err.usr_id = usr.user_id AND usr.hist_historic = 0")
				->left_join("hist_course crs")
					->on("err.crs_id = crs.crs_id AND crs.hist_historic = 0")
				->left_join("hist_usercoursestatus usrcrs")
					->on("err.usr_id = usrcrs.usr_id AND err.crs_id = usrcrs.crs_id AND usrcrs.hist_historic = 0")
				->left_join("usr_data ud")
					->on("err.usr_id = ud.usr_id")
				->compile();
		return $query;
	}

	protected function buildFilter($filter) {
		$filter ->static_condition("err.resolved = 0")
				->multiselect("reason"
							 , $this->plugin->txt("wbd_errors_reason")
							 , "reason"
							 , catFilter::getDistinctValues('reason', 'wbd_errors')
							 , array()
							 )
				->multiselect("action"
							 , $this->plugin->txt("wbd_errors_action")
							 , "action"
							 , catFilter::getDistinctValues('action', 'wbd_errors')
							 , array()
							 )
				->multiselect("internal"
							 , $this->plugin->txt("wbd_errors_internal")
							 , "internal"
							 , catFilter::getDistinctValues('internal', 'wbd_errors')
							 , array()
							 )
				->action($this->filter_action)
				->compile();
		return $filter;
	}

	public function fetchData(callable $callback) {
		/**
		 *	The following is not nice. I'll have to think of a better way to postprocess data from database, than the static transformResultRow.
		 *	It probably would suffice simply to make is nonstatic...
		 */
		$data = parent::fetchData($callback);
		$this->gCtrl->setParameterByClass("ilObjReportWBDErrorsGUI",$this->filter->getGETName(),$this->filter->encodeSearchParamsForGET());
		foreach ($data as &$rec) {
			$link_change_usr = $this->gCtrl->getLinkTargetByClass(
				array("iladministrationgui", "ilobjusergui"), "edit")
				.'&obj_id='.$rec['usr_id']
				.'&ref_id=7'; //ref 7: Manage user accounts here.
			$link_usr = '<a href="' .$link_change_usr.'">%s</a>';

			foreach (array('usr_id','firstname','lastname') as $key) {
				$rec[$key] = sprintf($link_usr, $rec[$key]);
			}

			$crs_ref_id = gevObjectUtils::getRefId($rec['crs_id']);
			if($crs_ref_id && $rec['crs_id'] > 0){
				$link_change_crs = $this->gCtrl->getLinkTargetByClass(
					array("ilrepositorygui", "ilobjcoursegui"), "editInfo")
					.'&ref_id='
					.$crs_ref_id;
				$link_change_crs = '<a href="' .$link_change_crs.'">%s</a>';
			} else {
				$link_change_crs = '%s';
			}
			$rec['crs_id'] = sprintf($link_change_crs, $rec['crs_id']);

			$rec['resolve'] = '<a href="' 
				.$this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportWBDErrorsGUI"), "resolve")
				.'&err_id='
				.$rec['id']
				.'">'
				.$this->plugin->txt("wbd_errors_resolve")
				.'</a>';

			if($this->gLng->exists($rec["reason_full"])) {
				$rec["reason_full"] = $this->gLng->txt($rec["reason_full"]);
			}

		}
		$this->gCtrl->setParameterByClass("ilObjReportWBDErrorsGUI",$this->filter->getGETName(),null);
		return $data;
	}
}