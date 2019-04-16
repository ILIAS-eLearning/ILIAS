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
			$error = $result->error();

			$message = $error;
			if ($error instanceof \Exception) {
				$message = $error->getMessage();
			}

			throw new ConstraintViolationException($message, 'error');
		}
		return $result->value();
	}
}
