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

class DictionaryTransformation implements Transformation
{
	/**
	 * @var Transformation
	 */
	private $transformation;

	/**
	 * @param Transformation $transformation
	 */
	public function __construct(Transformation $transformation)
	{
		$this->transformation = $transformation;
	}

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (false === is_array($from)) {
			throw new ConstraintViolationException(
				'The value MUST be an array',
				'not_array'
			);
		}

		$result = array();
		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				throw new ConstraintViolationException(
					'The key "%s" is NOT a string',
					'key_is_not_a_string'
				);
			}

			$transformedValue = $this->transformation->transform($value);
			$result[$key] = $transformedValue;
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		$from = $data->value();

		if (false === is_array($from)) {
			return new Result\Error(new \InvalidArgumentException('The value MUST be an array'));
		}

		$result = array();
		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				return new Result\Error(new \InvalidArgumentException(
					sprintf(
						'The key "%s" is NOT a string',
						$key
					)
				));
			}

			$resultObject = $this->transformation->applyTo(new Result\Ok($value));
			if (true === $resultObject->isError()) {
				return $resultObject;
			}

			$transformedValue = $resultObject->value();
			$result[$key] = $transformedValue;
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
