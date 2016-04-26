<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.setting.php';

class settingFloat extends setting {

	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return "";
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultToForm() {
		return function($val) {return number_format($val,2,",","");};
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm() {
		return function($val) {return (float)str_replace(",", ".", $val);};
	}
}