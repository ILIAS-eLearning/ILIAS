<?php

namespace ILIAS\TMS\Timezone;

/**
 * 
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class TimezoneDBImpl implements TimezoneDB{
	/**
	 * @var array<string, DateTime[]>
	 */
	static protected $default_times = array(
		"2017" => array(
			"start_summer" => "2017-03-26",
			"start_winter" => "2017-10-29"
		),
		"2018" => array(
			"start_summer" => "2018-03-25",
			"start_winter" => "2018-10-28"
		),
		"2019" => array(
			"start_summer" => "2019-03-31",
			"start_winter" => "2019-10-27"
		),
		"2020" => array(
			"start_summer" => "2020-03-29",
			"start_winter" => "2020-10-25"
		),
		"2021" => array(
			"start_summer" => "2021-03-28",
			"start_winter" => "2021-10-31"
		),
		"2022" => array(
			"start_summer" => "2022-03-27",
			"start_winter" => "2022-10-30"
		),
		"2023" => array(
			"start_summer" => "2023-03-26",
			"start_winter" => "2023-10-29"
		),
		"2024" => array(
			"start_summer" => "2024-03-31",
			"start_winter" => "2024-10-27"
		),
		"2025" => array(
			"start_summer" => "2025-03-30",
			"start_winter" => "2025-10-26"
		),
		"2026" => array(
			"start_summer" => "2026-03-29",
			"start_winter" => "2026-10-25"
		),
		"2027" => array(
			"start_summer" => "2027-03-28",
			"start_winter" => "2027-10-31"
		),
		"2028" => array(
			"start_summer" => "2028-03-26",
			"start_winter" => "2028-10-29"
		)
	);

	/**
	 * @inheritdoc
	 */
	public function readFor($year) {
		assert('is_string($year)');
		$times = $this->getTimes();
		if(!array_key_exists($year, $times)) {
			throw new \Exception("Unknown Year");
		}

		return $times[$year];
	}

	/**
	 * Creates an array with all summer and winter starts until 2028
	 *
	 * @return array<string, DateTime[]>
	 */
	protected function getTimes() {
		$ret = array();
		foreach(self::$default_times as $key => $times) {
			$n_times = array();
			$n_times["start_summer"] = $this->createDateTime($times["start_summer"]);
			$n_times["start_winter"] = $this->createDateTime($times["start_winter"]);
			$ret[$key] = $n_times;
		}

		return $ret;
	}

	/**
	 * Creates a DateTime object
	 *
	 * @param string 	$date
	 *
	 * @return \DateTime
	 */
	protected function createDateTime($date) {
		return \DateTime::createFromFormat("Y-m-d" , $date);
	}
}