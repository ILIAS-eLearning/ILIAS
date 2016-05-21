<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component;

/**
 * This tags a counter object.
 */
interface Counter extends \ILIAS\UI\Element {
	// Types of counters:
	const NOVELTY = "novelty";
	const STATUS = "status";

	/**
	 * Get the type of the counter.
	 *
	 * @return	string	One of the counter types.
	 */
	public function getType();

	/**
	 * Get a new counter resembling this one, but with a new type.
	 *
	 * TODO: Maybe this should go away. Why would i need to change the type
	 * of a counter after construction?
	 *
	 * @param	string	$type	One of counter types.
	 * @return	Counter
	 */
	public function withType($type);

	/**
	 * Get the number on the counter.
	 *
	 * @return	int
	 */
	public function getNumber();

	/**
	 * Get a new counter resembling this one, but with a new type.
	 *
	 * TODO: Maybe this should go away. Why would i need to change the number
	 * on a counter after construction?
	 *
	 * @param	int		$number
	 * @return	Counter
	 */
	public function withNumber($number);
}
