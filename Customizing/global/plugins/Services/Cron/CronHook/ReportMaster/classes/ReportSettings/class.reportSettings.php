<?php

class reportSettings extends reportSettingsContainer {

	protected $table_name;

	public static function create() {
		return new self;
	}

	public function setTable($table_name) {
		$this->table_name = $table_name;
	}

	public function table() {
		return $this->table_name;
	}

	public function setValue($setting_id, $value) {

	}

	public function defineSetting($setting_id) {
		return new settingsCreator($this);
	}

	public function type($setting_id) {
		return $this->settings[$setting_id]['type'];
	}

	public function name($setting_id) {
		return $this->settings[$setting_id]['name'];
	}

	public function postprocessing($setting_id) {
		return $this->settings[$setting_id]['postprocessing'];
	}

	public function settingIds() {
		return array_keys($this->settings);
	}

}



