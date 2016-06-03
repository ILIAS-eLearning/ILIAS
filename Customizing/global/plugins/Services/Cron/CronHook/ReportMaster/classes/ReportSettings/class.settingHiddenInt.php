<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingHidden.php';

class settingHiddenInt extends settingHidden {
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return 0;
	}
}