<?php
require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

const MIN_ROW = "3991";

class gevTrainerWorkloadGUI extends catBasicReportGUI{

	public function __construct() {

		parent::__construct();
		die(get_class($this));
	}
}
?>