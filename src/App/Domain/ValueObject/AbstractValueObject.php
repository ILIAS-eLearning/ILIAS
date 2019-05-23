<?php

use ILIAS\App\Domain\ValueObject\ValueObject;

class abstractValueObject implements ValueObject {
	/**
	 * Tells whether two Address are equal
	 *
	 * @param  ValueObject $value_object
	 * @return bool
	 */
	public function sameValueAs(ValueObject $value_object):bool {
		if (false === $this->classEquals($value_object)) {
			return false;
		}

		//TODO
	}


	/**
	 * @param ValueObject $value_object
	 *
	 * @return bool
	 */
	public function classEquals(ValueObject $value_object):bool
	{
		return \get_class($value_object) === \get_class($this);
	}
}

