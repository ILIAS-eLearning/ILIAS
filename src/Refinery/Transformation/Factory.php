<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation;

/**
 * Factory for basic transformations.
 * For purpose and usage see README.md
 */
class Factory {
	/**
	 * Add labels to an array.
	 *
	 * Will transform ["a","b"] to ["A" => "a", "B" => "b"] with $labels = ["A", "B"].
	 *
	 * @param   string[] $labels
	 * @return  Transformation
	 */
	public function addLabels(array $labels) {
		return new Transformations\AddLabels($labels, new \ILIAS\Data\Factory());
	}

	/**
     * Split string at given delimiter.
	 *
	 * Will transform "a,b,c" to ["a", "b", "c"] with $delim = ",".
	 *
	 * @param   string $delimiter
	 * @return  Transformation
	 */
	public function splitString($delimiter) {
		return new Transformations\SplitString($delimiter, new \ILIAS\Data\Factory());
	}

	/**
	 * Create a custom transformation.
	 *
	 * @param	callable $f	mixed -> mixed
	 * @return  Transformation
	 */
	public function custom(callable $f) {
		return new Transformations\Custom($f, new \ILIAS\Data\Factory());
	}

	/**
	 * Transform primitive value to data-type.
	 *
	 * @param	string $type
	 * @return  Transformation
	 */
	public function toData($type) {
		return new Transformations\Data($type, new \ILIAS\Data\Factory());
	}

	/**
	 * Transform a string to php \DateTimeImmutable; the string can be anything
	 * understood by php's DateTime::__construct.
	 *
	 * @return  Transformation
	 */
	public function toDateTime() {
		return new Transformations\DateTime(new \ILIAS\Data\Factory());
	}

	/**
	 * Adjust a date to reflect a certain timezone.
	 * This does not change the values of date, but sets the timezone.
	 *
	 * @return  Transformation
	 */
	public function toDateTimeWithTimezone(string $timezone) {
		return new Transformations\DateTimeWithTimezone($timezone, new \ILIAS\Data\Factory());
	}


}
