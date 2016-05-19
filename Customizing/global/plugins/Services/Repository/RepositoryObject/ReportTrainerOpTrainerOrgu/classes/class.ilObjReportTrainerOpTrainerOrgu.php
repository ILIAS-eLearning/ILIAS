<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrainerOpTepCat extends ilObjReportBase {
	const MIN_ROW = "0";
	protected $categories;
	protected $relevant_parameters = array();	

	public function initType() {
		 $this->setType("xoto");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_toto');
	}
}