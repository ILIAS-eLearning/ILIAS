<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component\Signal;
use InvalidArgumentException;
use Closure;

/**
 * Provides common functionality for component implementations.
 */
trait ComponentHelper
{
    /**
     * Default implementation uses the namespace of the component up to and excluding
     * "Component", reverses the order and adds spaces. Also does cache.
     */
    public function getCanonicalName(): string
    {
        return $this->getCanonicalNameByFullyQualifiedName();
    }

    /**
     * Does the calculation required for getCanonicalName.
     */
    protected function getCanonicalNameByFullyQualifiedName(): string
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
     * Throw an InvalidArgumentException containing the message if $check is false.
     *
     * @throws	InvalidArgumentException	if $check = false
     */
    protected function checkArg(string $which, bool $check, string $message): void
    {
        if (!$check) {
            throw new InvalidArgumentException("Argument '$which': $message");
        }
    }

    /**
     * Throw an InvalidArgumentException if $value is no string.
     *
     * @param	mixed	$value
     * @throws	InvalidArgumentException	if $value is no string
     */
    protected function checkStringArg(string $which, $value): void
    {
        $this->checkArg($which, is_string($value), $this->wrongTypeMessage("string", $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is no string or Signal.
     *
     * @param	mixed	$value
     * @throws	InvalidArgumentException	if $value is no string or Signal
     */
    protected function checkStringOrSignalArg(string $which, $value): void
    {
        $this->checkArg(
            $which,
            is_string($value) || $value instanceof Signal,
            $this->wrongTypeMessage("string or Signal", gettype($value))
        );
    }

    /**
     * Throw an InvalidArgumentException if $value is not a bool.
     *
     * @param	mixed	$value
     * @throws	InvalidArgumentException	if $value is no bool
     */
    protected function checkBoolArg(string $which, $value): void
    {
        $this->checkArg($which, is_bool($value), $this->wrongTypeMessage("bool", $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is not an instance of $class
     *
     * @param	mixed	$value
     * @throws	InvalidArgumentException	if $check = false
     */
    protected function checkArgInstanceOf(string $which, $value, string $class): void
    {
        $this->checkArg($which, $value instanceof $class, $this->wrongTypeMessage($class, $value));
    }

    /**
     * Throw an InvalidArgumentException if $value is not an element of array.
     *
     * @param	mixed	$value
     * @throws	InvalidArgumentException	if $check = false
     */
    protected function checkArgIsElement(string $which, $value, array $array, string $name): void
    {
        if (!is_object($value)) {
            $message = "expected $name, got '$value'";
        } else {
            $message = "expected $name, got object.";
        }
        $this->checkArg($which, in_array($value, $array), $message);
    }

    /**
     * Check every key and value of the list with a supplied closure.
     *
     * @param	Closure			$check		takes key and value, should return false if those don't fit
     * @param	Closure			$message	create an error message from key and value
     * @throws	InvalidArgumentException	if any element is not an instance of $classes
     */
    protected function checkArgList(string $which, array &$values, Closure $check, Closure $message): void
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
     * @param	string|string[]		$classes		name(s) of classes
     * @throws	InvalidArgumentException	if any element is not an instance of $classes
     */
    protected function checkArgListElements(string $which, array &$values, $classes): void
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
     */
    protected function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        return array($value);
    }

    /**
     * @param mixed $value
     */
    protected function wrongTypeMessage(string $expected, $value): string
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
