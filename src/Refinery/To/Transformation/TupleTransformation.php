<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Refinery\Validation\Constraints\IsArrayOfSameType;
use JaimePerez\TwigConfigurableI18n\Twig\Extensions\Node\Trans;

class TupleTransformation implements Transformation
{
	/**
	 * @var Transformation[]
	 */
	private $transformations;

	/**
	 * @param array $transformations
	 */
	public function __construct(array $transformations)
	{
		foreach ($transformations as $transformation) {
			if (!$transformation instanceof Transformation) {
				$transformationClassName = Transformation::class;

				throw new ConstraintViolationException(
					sprintf('The array MUST contain only "%s" instances', $transformationClassName),
					'not_a_transformation',
					$transformationClassName
				);
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
			$transformedValue = $this->transformations[$key]->transform($value);

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
			if (false === array_key_exists($key, $this->transformations)) {
				return new Result\Error(
					new ConstraintViolationException(
						sprintf(
							'There is no entry "%s" defined in the transformation array',
							$key
						),
						'values_do_not_match',
						$key
					)
				);
			}

			$resultObject = $this->transformations[$key]->applyTo(new Result\Ok($value));

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
