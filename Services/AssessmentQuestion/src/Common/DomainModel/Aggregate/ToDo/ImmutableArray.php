<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\AssessmentQuestion\Common\Entity;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use SplFixedArray;

abstract class ImmutableArray extends SplFixedArray implements Countable, Iterator, ArrayAccess {

	public function __construct(array $items) {
		parent::__construct(count($items));
		$i = 0;
		foreach ($items as $item) {
			$this->guardType($item);
			parent::offsetSet($i ++, $item);
		}
	}


	/**
	 * Throw when the item is not an instance of the accepted type.
	 *
	 * @param $item
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 */
	abstract protected function guardType($item): void;


	/**
	 * @return int
	 */
	final public function count(): int {
		return parent::count();
	}


	/**
	 * @return mixed
	 */
	final public function current() {
		return parent::current();
	}


	/**
	 * @return int
	 */
	final public function key(): int {
		return parent::key();
	}


	final public function next(): void {
		parent::next();
	}


	final public function rewind(): void {
		parent::rewind();
	}


	/**
	 * @return bool
	 */
	final public function valid(): bool {
		return parent::valid();
	}


	/**
	 * @param int|mixed $offset
	 *
	 * @return bool
	 */
	final public function offsetExists($offset): bool {
		return parent::offsetExists($offset);
	}


	/**
	 * @param int|mixed $offset
	 *
	 * @return mixed
	 */
	final public function offsetGet($offset) {
		return parent::offsetGet($offset);
	}


	/**
	 * @param int|mixed $offset
	 * @param mixed     $value
	 *
	 * @throws DomainExceptionArrayIsImmutable
	 */
	final public function offsetSet($offset, $value): void {
		throw new DomainExceptionArrayIsImmutable();
	}


	/**
	 * @param int|mixed $offset
	 *
	 * @throws DomainExceptionArrayIsImmutable
	 */
	final public function offsetUnset($offset): void {
		throw new DomainExceptionArrayIsImmutable();
	}
}