<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation\Transformations;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

/**
 * Transform value to php \DateTime
 */
class Date implements Transformation {

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		if($from) {
			return new \DateTime($from);
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from) {
		return $this->transform($from);
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		//TODO
		throw new \ILIAS\UI\NotImplementedException('NYI');
	}
}