<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;


use ILIAS\Data\Result;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

trait DeriveTransformFromApplyTo
{
	/**
	 * @param mixed $from
	 * @return Result
	 */
	public function transform($from)
	{
		/** @var Result $result */
		$result = $this->applyTo(new Result\Ok($from));
		if (true === $result->isError()) {
			throw new ConstraintViolationException($result->error(), 'error');
		}
		return new $result->value();
	}
}
