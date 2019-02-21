<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\Validation\Constraints\IsArrayOfSameType;

class TupleTransformation implements Transformation
{
	/**
	 * @var Transformation[]
	 */
	private $transformations;

	/**
	 * @var IsArrayOfSameType
	 */
	private $arrayOfSameType;

	/**
	 * @param array $transformations
	 * @param IsArrayOfSameType $arrayOfSameType
	 */
	public function __construct(array $transformations, IsArrayOfSameType $arrayOfSameType)
	{
		foreach ($transformations as $transformation) {
			if (!$transformation instanceof Transformation) {
				throw new \InvalidArgumentException(sprintf('The array element MUST be of type transformation "%s', Transformation::class));
			}
		}

		$this->transformations = $transformations;
		$this->arrayOfSameType = $arrayOfSameType;
	}

	/**
	 * @inheritdoc
	 * @throws \ilException
	 */
	public function transform($from)
	{
		$this->validateValueLength($from);

		$result = array();
		foreach ($from as $value) {
			$transformedValue = $value;
			foreach ($this->transformations as $transformation) {
				$transformedValue = $transformation->transform($transformedValue);
			}

			if ($value !== $transformedValue) {
				throw new \ilException(
					sprintf(
						'The origin value "%s" and transformed value "%s" are not strictly equal',
						$value,
						$transformedValue
					)
				);
			}

			$result[] = $transformedValue;
		}

		$isOk = $this->arrayOfSameType->applyTo(new Result\Ok($result));
		if (false === $isOk) {
			throw new \ilException('The values of the result MUST all be of the same type');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		$dataValue = $data->value();

		try {
			$this->validateValueLength($dataValue);
		} catch (\ilException $exception) {
			return new Result\Error($exception);
		}

		$result = array();
		foreach ($dataValue as $value) {
			$transformedValue = $value;
			foreach ($this->transformations as $transformation) {
				$resultObject = $transformation->applyTo(new Result\Ok($value));

				if ($resultObject->isError()) {
					return $resultObject;
				}

				$transformedValue = $resultObject->value();

				if ($value !== $transformedValue) {
					return new Result\Error(
						new \ilException(
							sprintf(
								'The origin value "%s" and transformed value "%s" are not strictly equal',
								$value,
								$transformedValue
							)
						)
					);
				}

				$transformedValue = $resultObject->value();
			}
			$result[] = $transformedValue;
		}

		$isOk = $this->arrayOfSameType->applyTo(new Result\Ok($result));
		if (false === $isOk) {
			return new Result\Error(new \ilException('The values of the result MUST all be of the same type'));
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
