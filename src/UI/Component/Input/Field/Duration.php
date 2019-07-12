<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\Data\DateFormat\DateFormat;

/**
 * This describes the duration input.
 */
interface Duration extends Group
{
	/**
	 * Get an input like this using the given format.
	 */
	public function withFormat(DateFormat $format) : Duration;

	/**
	 * Get the date-format of this input.
	 */
	public function getFormat(): DateFormat;

	/**
	 * Limit accepted values to Duration past (and including) the given $Duration.
	 */
	public function withMinValue(\DateTimeImmutable $date) : Duration;

	/**
	 * Return the lowest value the input accepts.
	 * @return  \Duration | null
	 */
	public function getMinValue();

	/**
	 * Limit accepted values to Duration before (and including) the given value.
	 */
	public function withMaxValue(\DateTimeImmutable $date) : Duration;

	/**
	 * Return the maximum date the input accepts.
	 * @return  \Duration | null
	 */
	public function getMaxValue();

	/**
	 * Input both date and time.
	 * @return  Duration
	 */
	public function withUseTime(bool $with_time) : Duration;

	/**
	 * Should the input be used to get both date and time?
	 * @return  Duration
	 */
	public function getUseTime(): bool;

	/**
	 * Use this Input for a time-value rather than a date.
	 * @return  Duration
	 */
	public function withTimeOnly(bool $time_only): Duration;

	/**
	 * Should the input be used to get a time only?
	 * @return  Duration
	 */
	public function getTimeOnly(): bool;

	/**
	 * Get an input like this using the given timezone.
	 */
	public function withTimezone(string $tz): Duration;

	/**
	 * Get the timezone of this input.
	 * @return null|string
	 */
	public function getTimezone();
}
