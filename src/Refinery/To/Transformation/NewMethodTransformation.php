<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Data\Result;
use ILIAS\DI\Exceptions\Exception;
use ILIAS\Refinery\Transformation\Transformation;

class NewMethodTransformation implements Transformation
{
	/**
	 * @var
	 */
	private $instance;

	/**
	 * @var
	 */
	private $method;

	/**
	 * @param $instance
	 * @param $methodToCall
	 * @throws \ilException
	 */
	public function __construct($instance, $methodToCall)
	{
		if (false === is_object($instance)) {
			throw new \ilException('The first parameter MUST be an object');
		}


		if (false === method_exists($instance, $methodToCall)) {
			throw new \ilException('The second parameter MUST be an method of the object');
		}

		$this->instance  = $instance;
		$this->method    = $methodToCall;
	}

	/**
	 * @inheritdoc
	 * @throws \ReflectionException
	 */
	public function transform($from)
	{
		$reflectionMethod = new \ReflectionMethod($this->instance, $this->method);
		return $reflectionMethod->invokeArgs($this->instance, $from);
	}

	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result
	{
		$value = $data->value();

		try {
			$reflectionMethod = new \ReflectionMethod($this->instance, $this->method);

			$value = $reflectionMethod->invokeArgs($this->instance, $value);
		} catch (\Exception $exception) {
			return new Result\Error($exception);
		}

		return new Result\Ok($value);
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
