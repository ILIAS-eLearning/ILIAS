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
		$result = array();
		foreach ($from as $value) {
			$transformedValue = $this->transformation->transform($value);
			$result[] = $transformedValue;
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
			$result[] = $transformedValue;
		}

		return new Result\Ok($result);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
