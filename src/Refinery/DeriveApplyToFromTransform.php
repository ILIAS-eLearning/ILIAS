<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\In\Transformation;

use ILIAS\Data\Result;

trait DeriveApplyToFromTransform
{
	/**
	 * @param Result $result
	 * @return Result
	 */
	public function applyTo(Result $result) : Result
	{
		try {
			$value = $this->transform($result->value());
		} catch (\Exception $exception) {
			return new Result\Error($exception);
		} catch (\Error $error) {
			return new Result\Error($error->getMessage());
		}

		return new Result\Ok($value);
	}
}
