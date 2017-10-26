<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

/**
 * Implementation of the state db over session.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSCancelPlayerStateDB implements Booking\ProcessStateDB {
	const SESSION_KEY = "tms_booking";

	protected function getKey($crs_id, $usr_id) {
		assert('is_int($crs_id)');
		assert('is_int($usr_id)');
		return self::SESSION_KEY."_".$crs_id."_".$usr_id;
	}

	/**
	 * @inheritdocs
	 */
	public function load($crs_id, $usr_id) {
		$key = $this->getKey($crs_id, $usr_id);
		$value = ilSession::get($key);
		if ($value === null) {
			return null;
		}
		$value = json_decode($value, true);
		assert('$value["crs_id"] === $crs_id');
		assert('$value["usr_id"] === $usr_id');
		return new Booking\ProcessState($crs_id, $usr_id, $value["step_number"], $value["step_data"]);
	}

	/**
	 * @inheritdocs
	 */
	public function save(Booking\ProcessState $state) {
		$key = $this->getKey($state->getCourseId(), $state->getUserId());
		$value =
			[ "crs_id" => $state->getCourseId()
			, "usr_id" => $state->getUserId()
			, "step_number" => $state->getStepNumber()
			, "step_data" => $state->getAllStepData()
			];
		ilSession::set($key, json_encode($value));
	}

	/**
	 * @inheritdocs
	 */
	public function delete(Booking\ProcessState $state) {
		$key = $this->getKey($state->getCourseId(), $state->getUserId());
		ilSession::clear($key);
	}
}

/**
 * cat-tms-patch end
 */
