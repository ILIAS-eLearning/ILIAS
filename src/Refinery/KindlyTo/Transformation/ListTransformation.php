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

class ListTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	/**
	 * @var Transformation
	 */
	private $transformation;

	public function __construct(Transformation $transformation)
	{
		$this->transformation = $transformation;
	}

	/**
	 * @inheritdoc
	 * @throws \ilException
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
	 * @throws \ilException
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
