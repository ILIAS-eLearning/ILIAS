<?php

namespace CaT\Plugins\TalentAssessment;

class ilActions {
	const F_TITLE = "title";
	const F_DESCRIPTION = "description";
	const F_CAREER_GOAL = "career_goal";
	const F_USERNAME = "username";
	const F_DATE = "date";
	const F_VENUE = "venue";
	const F_ORG_UNIT = "org_unit";
	const F_STATE = "state";
	const F_FIRSTNAME = "firstname";
	const F_LASTNAME = "lastname";
	const F_EMAIL = "email";

	const START_DATE = "start_date";
	const END_DATE = "end_date";

	public function __construct(\CaT\Plugins\TalentAssessment\ObjTalentAssessment $object
								, \CaT\Plugins\TalentAssessment\Settings\DB $settings_db) {
		$this->object = $object;
		$this->settings_db = $settings_db;
	}

	/**
	 * Update the object with the values from the array.
	 *
	 * @param	array	filled with fields according to F_*-constants
	 * @return  null
	 */
	public function update(array &$values) {
		assert('array_key_exists(self::F_TITLE, $values)');
		assert('is_string($values[self::F_TITLE])');
		$this->object->setTitle($values[self::F_TITLE]);
		if (array_key_exists(self::F_DESCRIPTION, $values)) {
			assert('is_string($values[self::F_DESCRIPTION])');
			$this->object->setDescription($values[self::F_DESCRIPTION]);
		}
		else {
			$this->object->setDescription("");
		}

		$start_date = $values[self::F_DATE]["start"]["date"];
		$start_time = $values[self::F_DATE]["start"]["time"];
		$values[self::START_DATE] = new \ilDateTime($start_date." ".$start_time,IL_CAL_DATETIME);

		$end_date = $values[self::F_DATE]["end"]["date"];
		$end_time = $values[self::F_DATE]["end"]["time"];
		$values[self::END_DATE] = new \ilDateTime($end_date." ".$end_time,IL_CAL_DATETIME);

		$this->object->updateSettings(function($s) use (&$values) {
			return $s
				->withCareerGoalID($values[self::F_CAREER_GOAL])
				->withUsername($values[self::F_USERNAME])
				->withStartDate($values[self::START_DATE])
				->withEndDate($values[self::END_DATE])
				->withVenue($values[self::F_VENUE])
				->withOrgUnit($values[self::F_ORG_UNIT])
				;
		});
		$this->object->update();
	}

	/**
	 * Read the object to an array.
	 *
	 * @return array
	 */
	public function read() {
		$values = array();
		$values[self::F_TITLE] = $this->object->getTitle();
		$values[self::F_DESCRIPTION] = $this->object->getDescription();

		$settings = $this->object->getSettings();
		$values[self::F_CAREER_GOAL] = $settings->getCareerGoalId();
		$values[self::F_USERNAME] = $settings->getUSername();

		$start_date = $settings->getStartDate()->get(IL_CAL_DATETIME);
		$start_date = explode(" ", $start_date);
		$end_date = $settings->getEndDate()->get(IL_CAL_DATETIME);
		$end_date = explode(" ", $end_date);

		$date = array("start" => array("date" => $start_date[0], "time" => $start_date[1])
					, "end" => array("date" => $end_date[0], "time" => $end_date[1])
									);

		$values[self::F_DATE] = $date;
		$values[self::F_VENUE] = $settings->getVenue();
		$values[self::F_ORG_UNIT] = $settings->getOrgUnit();
		$values[self::F_STATE] = $settings->getState();
		$values[self::F_FIRSTNAME] = $settings->getFirstname();
		$values[self::F_LASTNAME] = $settings->getLastname();
		$values[self::F_EMAIL] = $settings->getEmail();

		return $values;
	}

	/**
	 * @inheritdoc
	 */
	public function getCareerGoalsOptions() {
		return $this->settings_db->getCareerGoalsOptions();
	}
	
	/**
	 * @inheritdoc
	 */
	public function getVenueOptions() {
		return $this->settings_db->getVenueOptions();
	}
	
	/**
	 * @inheritdoc
	 */
	public function getOrgUnitOptions() {
		return $this->settings_db->getOrgUnitOptions();
	}
}