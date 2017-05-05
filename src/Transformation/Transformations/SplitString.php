<?php

namespace ILIAS\Transformation\Transformations;
use ILIAS\Transformation\Transformation;

class SplitString extends Transformation {
	/**
	 * @var string
	 */
	protected $delimiter;

	/**
	 * @param string 	$delimiter
	 */
	public function __construct($delimiter) {
		$this->delimiter = $delimiter;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		if(!is_string($from)) {
			throw new \InvalidArgumentException(__METHOD__." the argument is not a string").
		}

		return explode($this->delimiter, $from);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from) {
		if(!is_string($from)) {
			throw new \InvalidArgumentException(__METHOD__." the argument is not a string").
		}

		return explode($this->delimiter, $from);
	}
}