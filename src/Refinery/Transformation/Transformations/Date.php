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
		if($from) {
			$result = $this->attemptTransformation($from);
			if($result->isError()) {
				throw new \InvalidArgumentException($result->error(), 1);
			}
			return $result->value();
		}
		return null;
	}

	/**
	 * Try to execute the tranformation and return a Result.
	 * @param mixed $value
	 * @return Result
	 */
	private function attemptTransformation($value): Result
	{
		try {
			$value = new \DateTime($value);
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
		if($value instanceof \DateTime) {
			$value = date_format($value, "Y/m/d H:i:s");
		}
		return $this->attemptTransformation($value);
	}

}
