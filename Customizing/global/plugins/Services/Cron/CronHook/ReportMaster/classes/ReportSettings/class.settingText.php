<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.setting.php';

class settingText extends setting {

	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return "";
	}
}