<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Comparator;

/**
 * Class Base
 * @package ILIAS\Filesystem\Finder\Comparator
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class BaseComparator
{
	/** @var string */
	private $target = '';

	/** @var string */
	private $operator = '==';

	/**
	 * @return string
	 */
	public function getTarget(): string
	{
		return $this->target;
	}

	/**
	 * @param string $target
	 */
	public function setTarget(string $target)
	{
		$this->target = $target;
	}

	/**
	 * @return string
	 */
	public function getOperator(): string
	{
		return $this->operator;
	}

	/**
	 * @param string $operator
	 */
	public function setOperator(string $operator)
	{
		if (0 === strlen($operator)) {
			$operator = '==';
		}

		if (!in_array($operator, ['>', '<', '>=', '<=', '==', '!='])) {
			throw new \InvalidArgumentException(sprintf('Invalid operator "%s".', $operator));
		}

		$this->operator = $operator;
	}

	/**
	 * @param string $test
	 * @return bool
	 */
	public function test(string $test): bool
	{
		switch ($this->operator) {
			case '>':
				return $test > $this->target;
			case '>=':
				return $test >= $this->target;
			case '<':
				return $test < $this->target;
			case '<=':
				return $test <= $this->target;
			case '!=':
				return $test != $this->target;
		}

		return $test == $this->target;
	}
}