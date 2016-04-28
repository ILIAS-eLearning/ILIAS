<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.setting.php';

class settingString extends setting {
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return "";
	}
}