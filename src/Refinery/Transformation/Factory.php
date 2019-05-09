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
	 * Transform primitive value to data-type.
	 *
	 * @param	string $type
	 * @return  Transformation
	 */
	public function toData($type) {
		return new Transformations\Data($type, new \ILIAS\Data\Factory());
	}


}
