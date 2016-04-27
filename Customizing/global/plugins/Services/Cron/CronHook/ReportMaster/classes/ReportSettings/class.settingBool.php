<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.setting.php';

class settingBool extends setting {
	
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultToForm() {
		return function($val) {return 1;};
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm() {
		return function($val) {return $val ? true : false;};
	}
}