<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

class RecordTransformation implements Transformation
{
	/**
	 * @var Transformation[]
	 */
	private $transformations;

	/**
	 * @param Transformation[] $transformations
	 * @throws \ilException
	 */
	public function __construct(array $transformations)
	{
		foreach ($transformations as $key => $transformation) {
			if (!$transformation instanceof Transformation) {
				throw new \ilException(sprintf('The array element MUST be of type transformation "%s', Transformation::class));
			}

			if (false === is_string($key)) {
				throw new \ilException('The array key MUST be a string');
			}
		}

		$this->transformations = $transformations;
	}

	/**
	 * @inheritdoc
	 * @throws \ilException
	 */
	public function transform($from)
	{
		$result = array();

		$this->validateValueLength($from);

		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				throw new \ilException(
					sprintf(
						'The key "%s" is NOT a string',
						$key
					)
				);
			}

			if (false === isset($this->transformations[$key])) {
				throw new \ilException(
					sprintf(
						'The key "%s" is NOT a key for a transformation',
						$key
					)
				);
			}

			$transformation = $this->transformations[$key];
			$transformedValue = $transformation->transform($value);

			$result[$key] = $transformedValue;
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 * @throws \ilException
	 */
	public function applyTo(Result $data): Result
	{
		$from = $data->value();

		try {
			$this->validateValueLength($from);
		} catch (\ilException $exception) {
			return new Result\Error($exception);
		}

		$result = array();

		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				return new Result\Error(
					sprintf(
						'The key "%s" is NOT a string',
						$key
					)
				);
			}

			if (false === isset($this->transformations[$key])) {
				return new Result\Error(
					sprintf(
						'The key "%s" is NOT a key for a transformation',
						$key
					)
				);
			}


			$transformation = $this->transformations[$key];
			$resultObject = $transformation->applyTo(new Result\Ok($value));

			if (true === $resultObject->isError()) {
				return $resultObject;
			}

			$result[$key] = $resultObject->value();
		}

		return new Result\Ok($result);
	}

	/**
	 * @inheritdoc
	 * @throws \ilException
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}

	/**
	 * @param $values
	 * @throws \ilException
	 */
	private function validateValueLength($values)
	{
		$countOfValues = count($values);
		$countOfTransformations = count($this->transformations);

		if ($countOfValues !== $countOfTransformations) {
			throw new \ilException(
				sprintf(
					'The given values(count: "%s") does not match with the given transformations("%s")',
					$countOfValues,
					$countOfTransformations
				)
			);
		}
	}
}
