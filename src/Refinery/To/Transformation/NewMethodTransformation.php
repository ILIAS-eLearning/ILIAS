<?php
declare(strict_types=1);

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
			throw new \ilException('The first parameter MUST be an object');
		}


		if (false === method_exists($className, $methodToCall)) {
			throw new \ilException('The second parameter MUST be an method of the object');
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
	public function applyTo(Result $data): Result
	{
		$value = $data->value();

		try {
			$value = call_user_func_array(array($this->className, $this->method), $value);
		} catch (\Exception $exception) {
			return new Result\Error($exception);
		} catch (\Error $error) {
			return new Result\Error($error->getMessage());
		}

		return new Result\Ok($value);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
