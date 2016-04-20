<?php

class reportSettings {

	protected $settings;
	protected $database;
	protected $fnished;

	public static function create() {
		return new self;
	}

	public function setTable($table_name ) {

	}

	public function table($table_name ) {

	}

	public function setValue($setting_id, $value) {

	}

	public function defineSetting($setting_id) {

	}

	public function definitionFinished() {

	}

	public function value($setting_id) {

	}

	public function type($setting_id) {

	}

	public function name($setting_id) {

	}

	public function settingIds() {
		return array_keys($this->settings);
	}

	protected function addField($setting_id,array $setting_md) {

	}
}

class settingsCreator extends reportSettings {

	public function setting($setting_id) {
		throw new reportSettingsException("impossible atm");
	}

	public function name($name) {

	}
}