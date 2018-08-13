<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes the date-field.
 */
interface Date extends Input {

	/**
	 * Get an input like this using the given format.
	 */
	public function withFormat(string $format) : Date;

	/**
	 * Return the input's date-format
	 */
	public function getFormat() : string ;

	/**
	 * Limit accepted values to dates past given $date.
	 */
	public function withMinDate(\DateTime $date) : Date;

	/**
	 * Return the lowest date the input accepts.
	 * @return  \DateTime | null
	 */
	public function getMinDate();

	/**
	 * Limit accepted values to dates before given $date.
	 */
	public function withMaxDate(\DateTime $date) : Date;

	/**
	 * Return the maximum date the input accepts.
	 * @return  \DateTime | null
	 */
	public function getMaxDate();

	/**
	 * Render input with time-glyph (calendar-glyph otherwise).
	 * @return  Date
	 */
	public function withTimeGlyph(bool $use_time_glyph) : Date;


}
