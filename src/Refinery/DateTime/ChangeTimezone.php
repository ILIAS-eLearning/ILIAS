<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\DateTime;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;

/**
 * change the timezone of php's \DateTimeImmutable
 */
class ChangeTimezone implements Transformation
{
	/**
	 * @var \DateTimeZone
	 */
	private $timezone;

	/**
	 * @param string $timezone
	 * @param Factory $factory
	 */
	public function __construct(string $timezone, Factory $factory)
	{
		if(!in_array($timezone, timezone_identifiers_list())) {
			throw new \InvalidArgumentException("$timezone is not a valid timezone identifier", 1);
		}
		$this->timezone = new \DateTimeZone($timezone);
		$this->factory = $factory;
	}

	/**
	 * calculate the difference beween two timezones in seconds
	 */
	protected function getTimezoneDelta(\DateTimeZone $tz1, \DateTimeZone $tz2): int
	{
		$date1 = new \DateTimeImmutable('now', $tz1);
		$date2 = new \DateTimeImmutable('now', $tz2);
		$delta = $tz1->getOffset($date1) - $tz2->getOffset($date2);
		return $delta;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (!$from) {
			return null;
		}
		if (! $from instanceof \DateTimeImmutable) {
			throw new \InvalidArgumentException("$from is not a DateTime-object", 1);
		}
		return $this->performTransformation($from);
	}

	/**
	 * Do tranformation.
	 * @param \DateTimeImmutable $from
	 * @return \DateTimeImmutable
	 */
	protected function performTransformation(\DateTimeImmutable $from): \DateTimeImmutable
	{
		$offset = $this->getTimezoneDelta(
			$from->getTimezone(),
			$this->timezone
		);

		$to = clone $from;
		$to = $to
			->setTimezone($this->timezone)
			->modify("$offset seconds");
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
		$value = $data->value();
		if (! $value instanceof \DateTimeImmutable) {
			$exception = new \InvalidArgumentException("$value is not a DateTime-object", 1);
			return $this->factory->error($exception);
		}
		$value = $this->performTransformation($value);
		return $this->factory->ok($value);
	}

}
