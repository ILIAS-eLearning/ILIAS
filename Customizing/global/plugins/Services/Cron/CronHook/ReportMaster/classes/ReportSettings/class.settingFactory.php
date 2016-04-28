<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingInt.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingString.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingFloat.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingBool.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingListInt.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingRichText.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettings.php';

class settingFactory {
	protected $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	public function settingInt($id, $name) {
		return new settingInt($id, $name);
	}

	public function settingString($id, $name) {
		return new settingString($id, $name);
	}

	public function settingFloat($id, $name) {
		return new settingFloat($id, $name);
	}

	public function settingBool($id, $name) {
		return new settingBool($id, $name);
	}

	public function settingRichText($id, $name) {
		return new settingRichText($id, $name);
	}

	public function settingText($id, $name) {
		return new settingText($id, $name);
	}

	public function settingListInt($id, $name) {
		return new settingListInt($id, $name);
	}

	public function reportSettings($table) {
		return new reportSettings($table, $this->db);
	}

	public function reportSettingsDataHandler() {
		return new reportSettingsDataHandler($this->db);
	}

	public function reportSettingsFormHandler() {
		return new reportSettingsFormHandler();
	}
}