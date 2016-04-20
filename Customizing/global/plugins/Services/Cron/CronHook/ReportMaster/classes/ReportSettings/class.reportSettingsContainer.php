<?php

class reportSettingsContainer {
	protected $settings;

	protected function addField($setting_id, array $setting_md) {
		if(isset($this->settings[$settings_id])) {
			throw new reportSettingsException("not allowed to rewrite id $settings_id");
		}
		$this->settings[$settings_id] = $settings_md;
		return $this;
	}
}