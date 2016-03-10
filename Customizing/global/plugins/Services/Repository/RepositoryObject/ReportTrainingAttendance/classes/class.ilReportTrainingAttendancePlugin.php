<?php

require_once 'Services/ReportsRepository/classes/class.ilReportBasePlugin.php';

class ilReportTrainingAttendancePlugin extends ilReportBasePlugin {
	// must correspond to the plugin subdirectory
	protected function getReportName() {
		return "ReportTrainingAttendance";
	}
}