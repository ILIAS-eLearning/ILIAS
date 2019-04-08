<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\In\Transformation\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation\Transformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

class NewMethodTransformation implements Transformation
{
	use DeriveApplyToFromTransform;
	/**
	 * @var
	 */
	private $className;

	/**
	 * @var
	 */
	private $method;

	/**
	 * @param string $className
	 * @param string $methodToCall
	 * @throws \ilException
	 */
	public function __construct(string $className, string $methodToCall)
	{
		if (false === class_exists($className)) {
			throw new ConstraintViolationException(
				'The first parameter MUST be an object',
				'first_parameter_must_be_an_object'
			);
		}


		if (false === method_exists($className, $methodToCall)) {
			throw new ConstraintViolationException(
				'The second parameter MUST be an method of the object',
				'second_parameter_must_be_an_method'
			);
		}

		$this->className = $className;
		$this->method    = $methodToCall;
	}

	/**
	 * @inheritdoc
	 * @return mixed
	 */
	public function transform($from)
	{
		return call_user_func_array(array($this->className, $this->method), $from);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
