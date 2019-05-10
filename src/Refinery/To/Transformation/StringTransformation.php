<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class StringTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (false === is_string($from)) {
			throw new ConstraintViolationException(
				'The value MUST be of type string',
				'not_string'
			);
		}
		return (string) $from;
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
