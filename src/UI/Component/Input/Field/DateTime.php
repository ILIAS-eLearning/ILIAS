<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\Data\DateFormat\DateFormat;

/**
 * This describes the datetime-field.
 */
interface DateTime extends Input
{
	/**
	 * Get an input like this using the given format.
	 */
	public function withFormat(DateFormat $format) : DateTime;

	/**
	 * Get the date-format of this input.
	 */
	public function getFormat(): DateFormat;

	/**
	 * Return the datetime format in a form fit for the JS-component of this input.
	 * Currently, this means transforming the elements of DateFormat to momentjs.
	 *
	 * http://eonasdan.github.io/bootstrap-datetimepicker/Options/#format
	 * http://momentjs.com/docs/#/displaying/format/
	 */
	public function getTransformedFormat(): string;

	/**
	 * Limit accepted values to datetime past (and including) the given $datetime.
	 */
	public function withMinValue(\DateTime $datetime) : DateTime;

	/**
	 * Return the lowest value the input accepts.
	 * @return  \DateTime | null
	 */
	public function getMinValue();

	/**
	 * Limit accepted values to datetime before (and including) the given value.
	 */
	public function withMaxValue(\DateTime $datetime) : DateTime;

	/**
	 * Return the maximum date the input accepts.
	 * @return  \DateTime | null
	 */
	public function getMaxValue();

	/**
	 * Input both date and time.
	 * @return  DateTime
	 */
	public function withTime(bool $with_time) : DateTime;

	/**
	 * Should the input be used to get both date and time?
	 * @return  DateTime
	 */
	public function getUseTime(): bool;

	/**
	 * Use this Input for a time-value rather than a date.
	 * @return  DateTime
	 */
	public function withTimeOnly(bool $time_only): DateTime;

	/**
	 * Should the input be used to get a time only?
	 * @return  DateTime
	 */
	public function getTimeOnly(): bool;


}
