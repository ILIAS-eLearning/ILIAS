<?php

use CaT\Plugins\CareerGoal;

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * career goal plugin for repository
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilCareerGoalPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName() {
		return "CareerGoal";
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
	 * @return \CaT\Plugins\CareerGoal\Settings\DB
	 */
	public function getSettingsDB() {
		global $ilDB, $ilUser;
		if($this->settings_db === null) {
			$this->settings_db = new CareerGoal\Settings\ilDB($ilDB, $ilUser);
		}
		return $this->settings_db;
	}

	/**
	 * create (if not available) and returns RequirementsDB
	 *
	 * @return \CaT\Plugins\CareerGoal\Requirements\DB
	 */
	public function getRequirementsDB() {
		global $ilDB, $ilUser;
		if($this->requirements_db === null) {
			$this->requirements_db = new CareerGoal\Requirements\ilDB($ilDB, $ilUser);
		}
		return $this->requirements_db;
	}

	/**
	 * create (if not available) and returns ObservationsDB
	 *
	 * @return \CaT\Plugins\CareerGoal\Observations\DB
	 */
	public function getObservationsDB() {
		global $ilDB, $ilUser;
		if($this->observation_db === null) {
			$this->observation_db = new CareerGoal\Observations\ilDB($ilDB, $ilUser);
		}
		return $this->observation_db;
	}
}