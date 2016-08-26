<?php

use CaT\Plugins\TalentAssessment;

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * career goal plugin for repository
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilTalentAssessmentPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName() {
		return "TalentAssessment";
	}

	/**
	 * Get a closure to get txts from plugin.
	 *
	 * @return \Closure
	 */
	public function txtClosure() {
		return function($code) {
			return $this->txt($code);
		};
	}

	/**
	 * create (if not available) and returns SettingsDB
	 *
	 * @return \CaT\Plugins\TalentAssessment\Settings\DB
	 */
	public function getSettingsDB() {
		global $ilDB, $ilUser;
		if($this->settings_db === null) {
			$this->settings_db = new TalentAssessment\Settings\ilDB($ilDB, $ilUser);
		}
		return $this->settings_db;
	}
}