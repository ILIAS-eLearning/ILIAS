<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component;

/**
 * Provides common functionality for component implementations.
 */
trait ComponentHelper {
	/**
	 * Throw an InvalidArgumentException containing the message if $check is false.
     *
	 * @param	string	$which
	 * @param	bool	$check
	 * @param	string	$message
 	 * @throws 	\InvalidArgumentException	if $check = false
	 * @return	null
	 */
	protected function checkArg($which, $check, $message) {
		assert('is_string($which)');
		assert('is_bool($check)');
		assert('is_string($message)');
		if (!$check) {
			throw new \InvalidArgumentException("Argument '$which': $message");
		}
	}

	/**
	 * Throw an InvalidArgumentException if $value is no int.
	 *
	 * @param	string	$which
	 * @param	mixed	$value
 	 * @throws 	\InvalidArgumentException	if $check = false
	 * @return null
	 */ 
	protected function checkIntArg($which, $value) {
		$this->checkArg($which, is_int($value), $this->wrongTypeMessage("integer", $value));
	}

	/**
	 * Throw an InvalidArgumentException if $value is no string.
	 *
	 * @param	string	$which
	 * @param	mixed	$value
	 * @throws 	\InvalidArgumentException	if $check = false
	 * @return null
	 */
	protected function checkStringArg($which, $value) {
		$this->checkArg($which, is_string($value), $this->wrongTypeMessage("string", $value));
	}

	protected function wrongTypeMessage($expected, $value) {
		$type = gettype($value);
		return "expected $expected, got $type '$value'";
	}
}
