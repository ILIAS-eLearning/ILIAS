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

	/**
	 * Throw an InvalidArgumentException if $value is not an instance of $class
	 *
	 * @param	string	$which
	 * @param	mixed	$value
	 * @param	string	$class
	 * @throws 	\InvalidArgumentException	if $check = false
	 * @return  null
	 */
	protected function checkArgInstanceOf($which, $value, $class) {
		$this->checkArg($which, $value instanceof $class, $this->wrongTypeMessage($class, $value));
	}

	/**
	 * Throw an InvalidArgumentException if $value is not an element of array.
	 *
	 * @param	string	$which
	 * @param	mixed	$value
	 * @param	array	$array
	 * @param	string	$name		used in the exception
	 * @throws 	\InvalidArgumentException	if $check = false
	 * @return null
	 */
	protected function checkArgIsElement($which, $value, $array, $name) {
		if (!is_object($value)) {
			$message = "expected $name, got '$value'";
		}
		else {
			$message = "expected $name, got object."; 
		}
		$message = 
		$this->checkArg($which, in_array($value, $array), $message);
	}

	/**
	 * Check every element of the list if it is an instance of one of the given
	 * classes. Throw an InvalidArgumentException if that is not the case.
	 *
	 * @param	string				$which
	 * @param	mixed[]				&$values
	 * @param	string|string[]		$classes 		name(s) of classes
	 * @throws 	\InvalidArgumentException	if any element is not an instance of $classes
	 * @return	null
	 */
	protected function checkArgListElements($which, array &$values, $classes) {
		$failed = null;
		$classes = $this->toArray($classes);
		foreach ($values as $value) {
			$ok = false;
			foreach ($classes as $cls) {
				if ($value instanceof $cls) {
					$ok = true;
					break;
				}
			}
			if (!$ok) {
				$failed = $value;
				break;
			}
		}

		$this->checkArg($which, $failed === null, $this->wrongTypeMessage(implode(", ", $classes), $failed));
	}

	/**
 	 * Wrap the given value in an array if it is no array.
	 *
	 * @param	mixed	$value
	 * @return	array
	 */
	protected function toArray($value) {
		if (is_array($value)) {
			return $value;
		}
		return array($value);
	}

	protected function wrongTypeMessage($expected, $value) {
		$type = gettype($value);
		if (!is_object($value) && !is_array($value)) {
			return "expected $expected, got $type '$value'";
		}
		else {
			if (is_object($value)) {
				$type = get_class($value);
			}
			return "expected $expected, got $type";
		}
	}
}
