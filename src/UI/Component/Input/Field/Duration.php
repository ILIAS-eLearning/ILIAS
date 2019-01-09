<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes the duration input.
 */
interface Duration extends Group
{
	/**
	 * Get an input like this using the given format.
	 * Format is a string for moment.js's Format, see links below.
	 * example:
	 *'DD.MM.YYYY HH:mm' will display something like "22.08.2018 15:23"
	 *
	 * http://eonasdan.github.io/bootstrap-datetimepicker/Options/#format
	 * http://momentjs.com/docs/#/displaying/format/
	 */
	public function withFormat(string $format) : Duration;

	/**
	 * Return the input's date-format
	 */
	public function getFormat() : string;

	/**
	 * Limit accepted values to datetime past (and including) the given $datetime.
	 */
	public function withMinValue(\DateTime $datetime) : Duration;

	/**
	 * Return the lowest value the input accepts.
	 * @return  \DateTime | null
	 */
	public function getMinValue();

	/**
	 * Limit accepted values to datetime before (and including) the given value.
	 */
	public function withMaxValue(\DateTime $datetime) : Duration;

	/**
	 * Return the maximum date the input accepts.
	 * @return  \DateTime | null
	 */
	public function getMaxValue();

	/**
	 * Render input with time-glyph (calendar-glyph otherwise).
	 * @return  DateTime
	 */
	public function withTimeGlyph(bool $use_time_glyph) : Duration;

}
