<?php

require_once 'Services/ReportsRepository/classes/class.ilReportBasePlugin.php';

class ilReportCouponPlugin extends ilReportBasePlugin {
	// must correspond to the plugin subdirectory
	protected function getReportName() {
		return "ReportCoupon";
	}
}