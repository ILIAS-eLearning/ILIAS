<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;

/**
 * Transform value to php \DateTimeImmutable
 */
class DateTimeTransformation implements Transformation {

	/**
	 * @var DataFactory
	 */
	private $factory;

	/**
	 * @param Factory $factory
	 */
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from) {
		$result = $this->attemptTransformation($from);
		if($result->isError()) {
			throw new \InvalidArgumentException($result->error(), 1);
		}
		return $result->value();
	}

	/**
	 * Try to execute the transformation and return a Result.
	 * @param mixed $value
	 * @return Result
	 */
	protected function attemptTransformation($value): Result
	{
		try {
			$value = new \DateTimeImmutable($value);
		} catch (\Exception $e) {
			return $this->factory->error($e->getMessage());
		}
		return $this->factory->ok($value);
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
		$value = $data->value();
		if($value instanceof \DateTimeImmutable) {
			return $this->factory->ok($value);
		}
		return $this->attemptTransformation($value);
	}

}
