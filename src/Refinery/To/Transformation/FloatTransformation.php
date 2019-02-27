<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

class FloatTransformation implements Transformation
{
	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (false === is_float($from)) {
			throw new \InvalidArgumentException('The value MUST be of type float');
		}
		return (float) $from;
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		$value = $data->value();

		try {
			$resultValue = $this->transform($value);
		} catch (\Exception $exception) {
			return new Result\Error($exception);
		}

		return new Result\Ok($resultValue);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
