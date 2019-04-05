<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Data\Result;
use ILIAS\DI\Exceptions\Exception;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Refinery\Validation\Constraints\IsArrayOfSameType;

class ListTransformation implements Transformation
{
	/**
	 * @var Transformation
	 */
	private $transformation;

	/**
	 * @var IsArrayOfSameType
	 */
	private $arrayOfSameType;

	/**
	 * @param Transformation $transformation
	 * @param IsArrayOfSameType $arrayOfSameType
	 */
	public function __construct(Transformation $transformation, IsArrayOfSameType $arrayOfSameType)
	{
		$this->transformation = $transformation;
		$this->arrayOfSameType = $arrayOfSameType;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		$result = array();
		foreach ($from as $value) {
			$transformedValue = $this->transformation->transform($value);
			if ($transformedValue !== $value) {
				throw new ConstraintViolationException(
					'The transformed value "%s" does not match with the original value "%s"',
					'values_do_not_match',
					$transformedValue,
					$value
				);
			}
			$result[] = $value;
		}

		$isOk = $this->arrayOfSameType->applyTo(new Result\Ok($result));
		if (true === $isOk) {
			throw new ConstraintViolationException(
				'The values of the result MUST all be of the same type',
				'values_must_be_same_type'
			);
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		$from = $data->value();

		$result = array();
		foreach ($from as $value) {
			$resultObject = $this->transformation->applyTo(new Result\Ok($value));
			if (true === $resultObject->isError()) {
				return $resultObject;
			}

			$transformedValue = $resultObject->value();

			if ($transformedValue !== $value) {
				return new Result\Error(
					new ConstraintViolationException(
						'The transformed value "%s" does not match with the original value "%s"',
						'values_do_not_match',
						$transformedValue,
						$value
					)
				);
			}
			$result[] = $value;
		}

		$isOk = $this->arrayOfSameType->applyTo(new Result\Ok($result));
		if (true === $isOk) {
			return new Result\Error(
				new ConstraintViolationException(
					'The values of the result MUST all be of the same type',
					'values_must_be_same_type'
				)
			);
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
}
