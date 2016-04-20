<?php

class settingsCreator extends reportSettings {
	protected $obj;
	protected $settings_id;

	public function __construct(reportSettings $obj, $settings_id) {
		$this->obj = $obj;
		$this->settings_id = $settings_id;
	}

	public function withName($name) {
		if(isset($this->settings[$this->settings_id]['name'])) {
			throw new reportSettingsException('name allready set for $settings_id');
		}
		$this->settings[$this->settings_id]['name'] =  $name;
		return $this->return_obj();
	}

	public function withType($type) {
		if(isset($this->settings[$this->settings_id]['type'])) {
			throw new reportSettingsException('type allready set for $settings_id');
		}
		$this->settings[$this->settings_id]['type'] =  $type;
		return $this->return_obj();
	}

	public function withPostprocessing(closure $postprocessing) {
		if(isset($this->settings[$this->settings_id]['postprocessing'])) {
			throw new reportSettingsException('postprocessing allready set for $settings_id');
		}
		$this->settings[$this->settings_id]['postprocessing'] =  $postprocessing;
		return $this->return_obj();
	}

	protected function return_obj() {
		$settings = $this->settings[$settings_id];
		if(isset($settings['postprocessing']) && isset($settings['type']) && isset($settings['name'])) {
			$this->obj->setField($this->settings_id,$settings);
			return $this->obj;
		}
		return $this;
	}
}