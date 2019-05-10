<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;

class ListTransformation implements Transformation
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
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
