<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

class DictionaryTransformation implements Transformation
{
	/**
	 * @var Transformation
	 */
	private $transformation;

	/**
	 * @var \ILIAS\Refinery\Validation\Factory
	 */
	private $validationFactory;

	/**
	 * @param Transformation $transformation
	 * @param \ILIAS\Refinery\Validation\Factory $factory
	 */
	public function __construct(Transformation $transformation, \ILIAS\Refinery\Validation\Factory $factory)
	{
		$this->transformation = $transformation;
		$this->validationFactory = $factory;
	}

	/**
	 * @inheritdoc
	 * @throws \ilException
	 */
	public function transform($from)
	{
		if (false === is_array($from)) {
			throw new \InvalidArgumentException('The value MUST be an array');
		}

		$result = array();
		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				throw new \ilException(
					sprintf(
						'The key "%s" is NOT a string',
						$key
					)
				);
			}

			$transformedValue = $this->transformation->transform($value);
			if ($transformedValue !== $value) {
				throw new \ilException(
					sprintf(
						'The transformed value "%s" does not match with the original value "%s"',
						$transformedValue,
						$value
					)
				);
			}

			$result[$key] = $value;
		}

		$isOk = $this->validationFactory->isArrayOfSameType($result);

		if (false === $isOk) {
			return new \ilException((sprintf('The values of in the result are not of the same type')));
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
			return new Result\Error(new \ilException('The value MUST be an array'));
		}

		$result = array();
		foreach ($from as $key => $value) {
			if (false === is_string($key)) {
				return new Result\Error(new \ilException(
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

			if ($transformedValue !== $value) {
				return new Result\Error(new \ilException(
					sprintf(
						'The transformed value "%s" does not match with the original value "%s"',
						$transformedValue,
						$value
					)
				));
			}
			$result[$key] = $value;
		}

		$isOk = $this->validationFactory->isArrayOfSameType($result);

		if (false === $isOk) {
			return new Result\Error((sprintf('The values of in the result are not of the same type')));
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
