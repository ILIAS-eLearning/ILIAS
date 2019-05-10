<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class NewMethodTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	/**
	 * @var object
	 */
	private $object;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @param object $object
	 * @param string $methodToCall
	 */
	public function __construct(object $object, string $methodToCall)
	{
		if (false === method_exists($object, $methodToCall)) {
			throw new \InvalidArgumentException(
				'The second parameter MUST be an method of the object'
			);
		}

		$this->object = $object;
		$this->method    = $methodToCall;
	}

	/**
	 * @inheritdoc
	 * @return mixed
	 */
	public function transform($from)
	{
		return call_user_func_array(array($this->object, $this->method), $from);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
