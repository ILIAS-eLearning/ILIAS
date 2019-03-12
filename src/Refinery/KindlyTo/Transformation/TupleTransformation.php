<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo\Transformation;


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
	public function __construct(array $transformations)
	{
		foreach ($transformations as $transformation) {
			if (!$transformation instanceof Transformation) {
				throw new \InvalidArgumentException(sprintf('The array element MUST be of type transformation "%s', Transformation::class));
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
		$this->validateValueLength($from);

		$result = array();
		foreach ($from as $key => $value) {
			$transformedValue = $value;
			$transformedValue = $this->transformations[$key]->transform($transformedValue);

			$result[] = $transformedValue;
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
		foreach ($dataValue as $key => $value) {
			$transformedValue = $value;
			$resultObject = $this->transformations[$key]->applyTo(new Result\Ok($transformedValue));

			if ($resultObject->isError()) {
				return $resultObject;
			}

			$transformedValue = $resultObject->value();
			$result[] = $transformedValue;
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
