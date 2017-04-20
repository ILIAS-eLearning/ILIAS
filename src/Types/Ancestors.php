<?php

namespace ILIAS\Types;

interface Ancestors {

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return Type[]
	 */
	function getAncestors();
}