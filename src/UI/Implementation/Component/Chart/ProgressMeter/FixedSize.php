<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Component as C;
/**
 * Class ProgressMeter
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class FixedSize extends Standard implements C\Chart\ProgressMeter\FixedSize {

	/**
	 * @param float|int $width
	 * @return $this
	 */
	public function setWidth($width)
	{
		$this->width = $width;
		return $this;
	}

	/**
	 * @return float|int
	 */
	public function getWidth()
	{
		return $this->width;
	}
}