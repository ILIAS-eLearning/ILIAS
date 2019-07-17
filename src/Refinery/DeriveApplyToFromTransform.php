<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

use ILIAS\Data\Result;

trait DeriveApplyToFromTransform
{
	/**
	 * @param mixed $from
	 * @return mixed
	 * @throws \Exception
	 */
	abstract public function transform($from);

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
		}

		return new Result\Ok($value);
	}
}
