<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;

class NewObjectTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	private $className;

	/**
	 * @param string $className
	 */
	public function __construct(string $className)
	{
		$this->className  = $className;
	}

	/**
	 * @inheritdoc
	 * @throws \ReflectionException
	 */
	public function transform($from)
	{
		$class = new \ReflectionClass($this->className);
		$instance = $class->newInstanceArgs($from);

		return $instance;
	}

	/**
	 * @inheritdoc
	 * @throws \ReflectionException
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
