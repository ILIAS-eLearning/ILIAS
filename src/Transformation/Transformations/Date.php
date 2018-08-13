<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation\Transformations;
use ILIAS\Transformation\Transformation;

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
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from) {
		return $this->transform($from);
	}
}