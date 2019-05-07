<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Data\Result;
use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation\Transformation;

class StringTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (true === is_object($from)) {
			throw new \InvalidArgumentException('Can not cast an object to string');
		}

		if (true === is_array($from)) {
			throw new \InvalidArgumentException('Can not cast an array to string');
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
