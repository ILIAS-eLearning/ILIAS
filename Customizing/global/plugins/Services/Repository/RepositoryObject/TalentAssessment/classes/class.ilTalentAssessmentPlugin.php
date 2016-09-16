<?php

use CaT\Plugins\TalentAssessment;
use CaT\Plugins\CareerGoal;

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
			$career_goal_db = new CareerGoal\Settings\ilDB($ilDB, $ilUser);
			$this->settings_db = new TalentAssessment\Settings\ilDB($ilDB, $ilUser, $career_goal_db);
		}
		return $this->settings_db;
	}

	/**
	 * create (if not available) and returns ObservatorDB
	 *
	 * @return \CaT\Plugins\TalentAssessment\Observator\DB
	 */
	public function getObservatorDB() {
		global $ilDB, $ilUser;
		if($this->observator_db === null) {
			$this->observator_db = new TalentAssessment\Observator\ilDB($ilDB, $ilUser);
		}
		return $this->observator_db;
	}

	/**
	 * create (if not available) and returns ObservationsDB
	 *
	 * @return \CaT\Plugins\TalentAssessment\Observations\DB
	 */
	public function getObservationsDB() {
		global $ilDB, $ilUser;
		if($this->observations_db === null) {
			$base_observations_db = new CareerGoal\Observations\ilDB($ilDB, $ilUser);
			$this->observations_db = new TalentAssessment\Observations\ilDB($ilDB, $ilUser, $base_observations_db);
		}
		return $this->observations_db;
	}
}