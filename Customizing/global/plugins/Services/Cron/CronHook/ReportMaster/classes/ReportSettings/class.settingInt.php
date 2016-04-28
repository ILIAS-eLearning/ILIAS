<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.setting.php';

class settingInt extends setting {

	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return 0;
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm() {
		return function($val) {return (int)$val;};
	}
}