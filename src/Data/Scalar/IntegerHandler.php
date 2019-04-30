<?php
namespace ILIAS\Data\Scalar;

class IntegerHandler extends NumberHandler
{
	public function isInt() {
		return true;
	}
	public function toInt()
	{
		return $this->value;
	}
}
