<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;


use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation\Transformation;

class NewObjectTransformation implements Transformation
{
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
	 */
	public function applyTo(Result $data): Result
	{
		$value = $data->value();

		try {
			$class = new \ReflectionClass($this->className);
			$instance = $class->newInstanceArgs($value);
		} catch (\ReflectionException $e) {
			return new Result\Error($e);
		}

		return new Result\Ok($instance);
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
