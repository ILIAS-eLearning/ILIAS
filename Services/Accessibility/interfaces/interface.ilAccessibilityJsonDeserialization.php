<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityJsonDeserialization
 */
interface ilAccessibilityJsonDeserialization
{
	/**
	 * @param string $json
	 */
	public function fromJson(string $json) : void;
}