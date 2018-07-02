<?php

namespace ILIAS\TMS\Timezone;

/**
 * 
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class TimezoneCheckerImpl implements TimezoneChecker {

	/**
	 * @var TimezoneDB
	 */
	protected $timezone_db;

	public function __construct(TimezoneDB $timezone_db) {
		$this->timezone_db = $timezone_db;
	}

	/**
	 * @inheritdoc
	 */
	public function isSummerTime(\DateTime $date) {
		$times_for_year = $this->timezone_db->readFor($date->format("Y"));
		$start_summer = $times_for_year["start_summer"];
		$start_winter = $times_for_year["start_winter"];

		if($date->format("Y-m-d") >= $start_summer->format("Y-m-d")
			&& $date->format("Y-m-d") < $start_winter->format("Y-m-d")
		) {
			return true;
		}

		return false;
	}
}