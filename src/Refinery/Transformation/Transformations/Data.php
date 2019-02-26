<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation\Transformations;

use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Data\Factory as DataFactory;

/**
 * Convert a primitive to a data type.
 */
class Data implements Transformation {
	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @var DataFactory|Factory|null
	 */
	private $factory;

	/**
	 * @param string $type
	 * @param DataFactory $factory
	 */
	public function __construct($type, DataFactory $factory) {
		$this->type = $type;
		if(! method_exists($this->getDataFactory(), $type)) {
			throw new \InvalidArgumentException("No such type to transform to: $type");
		}

		$this->factory = $factory;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		$type = $this->type;
		$data_factory = $this->getDataFactory();
		return $data_factory->$type($from);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from) {
		return $this->transform($from);
	}

	/**
	 * Get an instance of the data-factory
	 * @return DataFactory
	 */
	protected function getDataFactory() {
		return new DataFactory();
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data) : Result
	{
		$dataValue = $data->value();
		if(false === method_exists($this->factory, $this->type)) {
			$exception = new \InvalidArgumentException(__METHOD__ . " the method does NOT exist.");
			return $this->factory->error($exception);
		}

		$type = $this->type;
		return $this->factory->$type($dataValue);
	}
}
