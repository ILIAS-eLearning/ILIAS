<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation\Transformations;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

/**
 * Adds to any array keys for each value
 */
class AddLabels implements Transformation {
	/**
	 * @var string[] | int[]
	 */
	protected $labels;

	/**
	 * @var Factory
	 */
	private $factory;

	/**
	 * @param string[] | int[] $labels
	 * @param Factory|null $factory
	 */
	public function __construct(array $labels, Factory $factory = null) {
		$this->labels = $labels;
		if (null === $factory) {
			$factory = new Factory();
		}
		$this->factory = $factory;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		if(!is_array($from)) {
			throw new \InvalidArgumentException(__METHOD__ . " argument is not an array.");
		}

		if(count($from) != count($this->labels)) {
			throw new \InvalidArgumentException(__METHOD__ . " number of items in arrays are not equal.");
		}

		return array_combine($this->labels, $from);
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
	public function applyTo(Result $data) : Result
	{
		$dataValue = $data->value();
		if(false === is_array($dataValue)) {
			$exception = new \InvalidArgumentException(__METHOD__." argument is not an array.");
			return $this->factory->error($exception);
		}

		if(count($dataValue) != count($this->labels)) {
			$exception = new \InvalidArgumentException(__METHOD__ . " number of items in arrays are not equal.");
			return $this->factory->error($exception);
		}

		$value = array_combine($this->labels, $dataValue);
		$result = $this->factory->ok($value);

		return $result;
	}
}
