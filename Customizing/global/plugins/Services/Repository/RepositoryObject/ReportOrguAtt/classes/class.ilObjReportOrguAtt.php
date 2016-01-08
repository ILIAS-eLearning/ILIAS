<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportOrguAtt extends ilObjReportBase {
	protected $relevant_parameters = array();


	public function initType() {
		 $this->setType("xroa");
	}
}