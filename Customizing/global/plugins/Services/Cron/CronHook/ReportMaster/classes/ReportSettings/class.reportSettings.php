<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettingsException.php';

class reportSettings {

	protected $table_name;
	protected $settings;
	protected $db;

	public function __construct($table_name, $db) {
		if(!$db->tableExists($table_name)) {
			throw new reportSettingsException("invalit value for table name, table $table_name does not exist");
		}
		$this->db = $db;
		$this->table_name = $table_name;
		$this->settings = array();
	}

	public function table() {
		return $this->table_name;
	}

	public function addSetting(setting $setting) {
		$id = $setting->id();
		if(!$this->db->tableColumnExists($this->table_name, $id)) {
			throw new reportSettingsException("invalid value for id, column $id in table $this->table_name does not exist");
		}
		$this->settings[$id] = $setting;
		return $this;
	}

	public function setting($id) {
		return $this->settings[$id];
	}

	public function settingIds() {
		return array_keys($this->settings);
	}
}