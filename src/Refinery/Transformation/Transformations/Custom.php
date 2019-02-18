<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Transformation\Transformations;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

/**
 * Transform values according to custom configuration
 */
class Custom implements Transformation {
	/**
	 * @var callable
	 */
	protected $transform;
	private $factory;

	/**
	 * @param callable $transform
	 * @param Factory|null $factory
	 */
	public function __construct(callable $transform, Factory $factory = null) {
		if (null === $factory) {
			$factory = new Factory();
		}
		$this->factory = $factory;

		$this->transform = $transform;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		return call_user_func($this->transform, $from);
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
		if(false === is_callable($dataValue)) {
			$exception = new \InvalidArgumentException(__METHOD__ . " argument is not a callable.");
			return $this->factory->error($exception);
		}

		$value = call_user_func($this->transform, $dataValue);
		$result = $this->factory->ok($value);

		return $result;
	}
}
