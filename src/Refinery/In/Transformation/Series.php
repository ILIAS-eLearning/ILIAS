<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\In\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

class Series implements Transformation
{
	/**
	 * @var Transformation[]
	 */
	private $transformationStrategies;

	/**
	 * @param array $transformations
	 * @throws \ilException
	 */
	public function __construct(array $transformations)
	{
		foreach ($transformations as $transformation) {
			if (!$transformation instanceof Transformation) {
				throw new \InvalidArgumentException(sprintf('The array MUST contain only "%s" instances', Transformation::class));
			}
		}
		$this->transformationStrategies = $transformations;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		$result = $from;
		foreach ($this->transformationStrategies as $strategy) {
			$result = $strategy->transform($result);
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		foreach ($this->transformationStrategies as $strategy) {
			$resultObject = $strategy->applyTo($data);
			if ($resultObject->isError()) {
				return $resultObject;
			}

			$data = $resultObject;
		}

		return new Result\Ok($data->value());
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
