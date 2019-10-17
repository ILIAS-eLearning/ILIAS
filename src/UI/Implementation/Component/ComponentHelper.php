<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component\Signal;

/**
 * Provides common functionality for component implementations.
 */
trait ComponentHelper
{
    /**
     * @var string|null
     */
    private $canonical_name = null;

    /**
     * Default implementation uses the namespace of the component up to and excluding
     * "Component", reverses the order and adds spaces. Also does caching.
     *
     * @return string
     */
    public function getCanonicalName()
    {
        if ($this->canonical_name === null) {
            $this->canonical_name = $this->getCanonicalNameByFullyQualifiedName();
        }
        return $this->canonical_name;
    }

    /**
     * Does the calculation required for getCanonicalName.
     *
     * @return	string
     */
    protected function getCanonicalNameByFullyQualifiedName()
    {
        $cls = explode("\\", get_class($this));
        $name = [];
        $cur = array_pop($cls);
        while ($cur !== "Component" && count($cls) > 0) {
            $name[] = preg_replace("%([a-z])([A-Z])%", "$1 $2", $cur);
            $cur = array_pop($cls);
        }
        return implode(" ", $name);
    }

    /**

    /**
     * Throw an InvalidArgumentException containing the message if $check is false.
     *
     * @param	string	$which
     * @param	bool	$check
     * @param	string	$message
     * @throws	\InvalidArgumentException	if $check = false
     * @return	null
     */
    protected function checkArg($which, $check, $message)
    {
        assert(is_string($which));
        assert(is_bool($check));
        assert(is_string($message));
        if (!$check) {
            throw new \InvalidArgumentException("Argument '$which': $message");
        }
    }

    /**
     * Throw an InvalidArgumentException if $value is no int.
     *
     * @param	string	$which
     * @param	mixed	$value
     * @throws	\InvalidArgumentException	if $value is no int
     * @return null
     */
    protected function checkIntArg($which, $value)
    {
        $this->checkArg($which, is_int($value), $this->wrongTypeMessage("integer", $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is no string.
     *
     * @param	string	$which
     * @param	mixed	$value
     * @throws	\InvalidArgumentException	if $value is no string
     * @return null
     */
    protected function checkStringArg($which, $value)
    {
        $this->checkArg($which, is_string($value), $this->wrongTypeMessage("string", $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is no string or Signal.
     *
     * @param	string	$which
     * @param	mixed	$value
     * @throws	\InvalidArgumentException	if $value is no string or Signal
     * @return null
     */
    protected function checkStringOrSignalArg($which, $value)
    {
        $this->checkArg(
            $which,
            is_string($value) || $value instanceof Signal,
            $this->wrongTypeMessage("string or Signal", gettype($value))
        );
    }

    /**
     * Throw an InvalidArgumentException if $value is not a float.
     *
     * @param	string	$which
     * @param	mixed	$value
     * @throws	\InvalidArgumentException	if $value is no float
     * @return	null
     */
    protected function checkFloatArg($which, $value)
    {
        $this->checkArg($which, is_float($value), $this->wrongTypeMessage("float", $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is not a bool.
     *
     * @param	string	$which
     * @param	mixed	$value
     * @throws	\InvalidArgumentException	if $value is no bool
     * @return	null
     */
    protected function checkBoolArg($which, $value)
    {
        $this->checkArg($which, is_bool($value), $this->wrongTypeMessage("bool", $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is not an instance of $class
     *
     * @param	string	$which
     * @param	mixed	$value
     * @param	string	$class
     * @throws	\InvalidArgumentException	if $check = false
     * @return	null
     */
    protected function checkArgInstanceOf($which, $value, $class)
    {
        $this->checkArg($which, $value instanceof $class, $this->wrongTypeMessage($class, $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is not an element of array.
     *
     * @param	string	$which
     * @param	mixed	$value
     * @param	array	$array
     * @param	string	$name		used in the exception
     * @throws	\InvalidArgumentException	if $check = false
     * @return null
     */
    protected function checkArgIsElement($which, $value, $array, $name)
    {
        if (!is_object($value)) {
            $message = "expected $name, got '$value'";
        } else {
            $message = "expected $name, got object.";
        }
        $message =
        $this->checkArg($which, in_array($value, $array), $message);
    }

    /**
     * Check every key and value of the list with a supplied closure.
     *
     * @param	string				$which
     * @param	mixed[]				&$values
     * @param	\Closure			$check		takes key and value, should return false if those don't fit
     * @param	\Closure			$message	create an error message from key and value
     * @throws	\InvalidArgumentException	if any element is not an instance of $classes
     * @return	null
     */
    protected function checkArgList($which, array &$values, \Closure $check, \Closure $message)
    {
        $failed_k = null;
        $failed_v = null;
        foreach ($values as $key => $value) {
            $ok = $check($key, $value);
            if (!$ok) {
                $failed_k = $key;
                $failed_v = $value;
                break;
            }
        }

        if ($failed_k !== null) {
            $m = $message($failed_k, $failed_v);
        } else {
            $m = "";
        }

        $this->checkArg($which, $failed_k === null, $m);
    }

    /**
     * Check every element of the list if it is an instance of one of the given
     * classes. Throw an InvalidArgumentException if that is not the case.
     *
     * @param	string				$which
     * @param	mixed[]				&$values
     * @param	string|string[]		$classes		name(s) of classes
     * @throws	\InvalidArgumentException	if any element is not an instance of $classes
     * @return	null
     */
    protected function checkArgListElements($which, array &$values, &$classes)
    {
        $classes = $this->toArray($classes);
        $this->checkArgList($which, $values, function ($_, $value) use (&$classes) {
            foreach ($classes as $cls) {
                if ($cls === "string" && is_string($value)) {
                    return true;
                }
                if ($cls === "int" && is_int($value)) {
                    return true;
                } elseif ($value instanceof $cls) {
                    return true;
                }
            }
            return false;
        }, function ($_, $failed) use (&$classes) {
            return $this->wrongTypeMessage(implode(", ", $classes), $failed);
        });
    }

    /**
     * Wrap the given value in an array if it is no array.
     *
     * @param	mixed	$value
     * @return	array
     */
    protected function toArray($value)
    {
        if (is_array($value)) {
            return $value;
        }
        return array($value);
    }

    protected function wrongTypeMessage($expected, $value)
    {
        $type = gettype($value);
        if (!is_object($value) && !is_array($value)) {
            return "expected $expected, got $type '$value'";
        } else {
            if (is_object($value)) {
                $type = get_class($value);
            }
            return "expected $expected, got $type";
        }
    }
}
