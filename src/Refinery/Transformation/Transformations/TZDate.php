<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation\Transformations;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

/**
 * change the timezone of php's \DateTime
 */
class TZDate implements Transformation
{

	private $timezone;

	public function __construct(string $timezone)
	{
		if(!in_array($timezone, timezone_identifiers_list())) {
			throw new \invalidArgumentException("$timezone is not a valid timezone identifier", 1);
		}
		$this->timezone = new \DateTimeZone($timezone);
	}


	/**
	 * calculate the difference beween two timezones in seconds
	 */
	protected function getTimezoneDeltaInHours(\DateTimeZone $tz1, \DateTimeZone $tz2): int
	{
		$date1 = new \DateTime('now', $tz1);
		$date2 = new \DateTime('now', $tz2);
		$delta = $tz1->getOffset($date1) - $tz2->getOffset($date2);
		return $delta;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if(!$from) {
			return null;
		}
		$offset = $this->getTimezoneDeltaInHours(
			$from->getTimezone(),
			$this->timezone
		);

		$to = clone $from;
		$to->setTimezone($this->timezone);
		$to = $to->modify("$offset seconds");
		return $to;
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from) {
		return $this->transform($from);
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		//TODO
		throw new \ILIAS\UI\NotImplementedException('NYI');
	}
}