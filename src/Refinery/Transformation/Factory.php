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
	 * Transform primitive value to data-type.
	 *
	 * @param	string $type
	 * @return  Transformation
	 */
	public function toData($type) {
		return new Transformations\Data($type, new \ILIAS\Data\Factory());
	}


}
