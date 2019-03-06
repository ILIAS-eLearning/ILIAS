<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

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
	 * @throws \ilException
	 */
	public function transform($from)
	{
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
