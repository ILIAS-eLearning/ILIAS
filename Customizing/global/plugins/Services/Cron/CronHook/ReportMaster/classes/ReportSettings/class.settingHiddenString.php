<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingHidden.php';

class settingHiddenString extends settingHidden {
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return "";
	}
}