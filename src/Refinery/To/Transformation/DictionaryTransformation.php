<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

class DictionaryTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

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
	 * @throws \ilException
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
