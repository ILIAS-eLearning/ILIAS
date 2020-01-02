<?php

namespace ILIAS\FileUpload;

/**
 * Trait ScalarTypeCheckAware
 *
 * This trait enables classes to check the types of variables.
 * All methods throw an IllegalArgumentException in order to indicate the type mismatch.
 *
 * This trait will be replaced with the native scalar types once ILIAS drops PHP 5.6 support.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @Internal
 */
trait ScalarTypeCheckAware
{

    /**
     * Type check for string variables.
     *
     * @param string $variable The variable which should be tested.
     * @param string $name     The name of the variable which is tested.
     *
     * @throws \InvalidArgumentException Thrown if the variable is not of the type string.
     * @since 5.3
     */
    private function stringTypeCheck($variable, $name)
    {
        if (!is_string($variable)) {
            $varType = gettype($variable);
            throw new \InvalidArgumentException("The $name must be of type string but $varType was given.");
        }
    }


    /**
     * Type check for int variables.
     *
     * @param string $variable The variable which should be tested.
     * @param string $name     The name of the variable which is tested.
     *
     * @throws \InvalidArgumentException Thrown if the variable is not of the type int.
     * @since 5.3
     */
    private function intTypeCheck($variable, $name)
    {
        if (!is_int($variable)) {
            $varType = gettype($variable);
            throw new \InvalidArgumentException("The $name must be of type integer but $varType was given.");
        }
    }
}
