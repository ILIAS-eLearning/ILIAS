<?php

namespace ILIAS\Transformation\Transformations;
use ILIAS\Transformation\Transformation;

class AddLabels extends Transformation {
	/**
	 * @var string[] | int[]
	 */
	protected $labels;

	/**
	 * @param string[] | int[]	$labels
	 */
	public function __construct(array $labels) {
		$this->labels = $labels;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		if(!is_array($from)) {
			throw new \InvalidArgumentException(__METHOD_." argument is not an array.");
		}

		if(count($from) != count($this->labels)) {
			throw new \InvalidArgumentException(__METHOD__." number of items in arrays are not equal");
		}

		return array_combine($this->labels, $from);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke(array $from) {
		if(!is_array($from)) {
			throw new \InvalidArgumentException(__METHOD_." argument is not an array.");
		}

		if(count($from) != count($this->labels)) {
			throw new \InvalidArgumentException(__METHOD__." number of items in arrays are not equal");
		}

		return array_combine($this->labels, $from);
	}
}
