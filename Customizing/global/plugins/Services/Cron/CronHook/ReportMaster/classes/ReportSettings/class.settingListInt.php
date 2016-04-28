<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.setting.php';

class settingListInt extends setting {
	protected $options = array();
	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue() {
		return 0;
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultToForm() {
		return function($val) {return (int)$val;};
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm() {
		return function($val) {return (int)$val;};
	}

	/**
	 * Provide the setting with a choice of options to be presented in form.
	 * array options should be value => lable.
	 * @param 	int|string[] 	$options
	 */
	public function setOptions(array $options) {
		$this->options = $options;
		return $this;
	}
	
	/**
	 * Get options connected to this setting.
	 * @param 	int|string[] 	$options
	 */
	public function options() {
		return $this->options;
	}
}