<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Object;

use JMS\Serializer\SerializerBuilder;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;

/**
 * Split a string by delimiter into array
 */
class JsonSerializedObject implements Transformation {
	/**
	 * @var object
	 */
	protected $delimiter;

	/**
	 * @var Factory
	 */
	private $factory;

	/**
	 * @param Factory $factory
	 */
	public function __construct(Factory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		if(!is_object($from)) {
			throw new \InvalidArgumentException(__METHOD__ . " the argument is not an object.");
		}

		$serializer = SerializerBuilder::create()->build();
		return $serializer->serialize($from, 'json');
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
		$dataValue = $data->value();
		if(false === is_object($dataValue)) {
			$exception = new \InvalidArgumentException(__METHOD__ . " the argument is not an object.");
			return $this->factory->error($exception);
		}

		$serializer = SerializerBuilder::create()->build();
		$value = $serializer->serialize($dataValue, 'json');
		return $this->factory->ok($value);
	}
}
