<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportBookingsByTpl extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $sum_parts = array();

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		require_once $this->plugin->getDirectory().'/config/cfg.bk_by_tpl.php';
	}

	public function initType() {
		 $this->setType("xrbt");
	}

	public function prepareReport() {
		$this->sum_table = $this->buildSumTable(catReportTable::create());
		parent::prepareReport();
	}
}