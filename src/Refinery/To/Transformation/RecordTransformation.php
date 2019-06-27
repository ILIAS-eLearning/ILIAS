<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class RecordTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	/**
	 * @var Transformation[]
	 */
	private $transformations;

	/**
	 * @param Transformation[] $transformations
	 */
	public function __construct(array $transformations)
	{
		foreach ($transformations as $key => $transformation) {
			if (!$transformation instanceof Transformation) {
				$transformationClassName = Transformation::class;

				throw new ConstraintViolationException(
					sprintf('The array MUST contain only "%s" instances', $transformationClassName),
					'not_a_transformation',
					$transformationClassName
				);
			}

			if (false === is_string($key)) {
				throw new ConstraintViolationException(
					'The array key MUST be a string',
					'key_is_not_a_string'
				);
			}
		}

		$this->transformations = $transformations;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		$result = array();

		$this->validateValueLength($from);

		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				throw new ConstraintViolationException(
					'The array key MUST be a string',
					'key_is_not_a_string'
				);
			}

			if (false === isset($this->transformations[$key])) {
				throw new ConstraintViolationException(
					sprintf('Could not find transformation for array key "%s"', $key),
					'array_key_does_not_exist',
					$key
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
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}

	/**
	 * @param $values
	 * @throws ConstraintViolationException
	 */
	private function validateValueLength($values)
	{
		$countOfValues = count($values);
		$countOfTransformations = count($this->transformations);

		if ($countOfValues !== $countOfTransformations) {
			throw new ConstraintViolationException(
				sprintf(
					'The given values(count: "%s") does not match with the given transformations("%s")',
					$countOfValues,
					$countOfTransformations
				),
				'length_does_not_match',
				$countOfValues,
				$countOfTransformations
			);
		}
	}
}
