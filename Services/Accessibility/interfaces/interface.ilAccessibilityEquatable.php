<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityEquatable
 */
interface ilAccessibilityEquatable
{
	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function equals($other) : bool;
}