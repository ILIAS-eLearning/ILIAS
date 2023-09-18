<?php

namespace ILIAS\FileUpload;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    private function stringTypeCheck(string $variable, string $name): void
    {
        if (!is_string($variable)) {
            $varType = gettype($variable);
            throw new \InvalidArgumentException("The $name must be of type string but $varType was given.");
        }
    }


    /**
     * Type check for int variables.
     *
     * @param int    $variable The variable which should be tested.
     * @param string $name     The name of the variable which is tested.
     *
     * @throws \InvalidArgumentException Thrown if the variable is not of the type int.
     * @since 5.3
     */
    private function intTypeCheck(int $variable, string $name): void
    {
        if (!is_int($variable)) {
            $varType = gettype($variable);
            throw new \InvalidArgumentException("The $name must be of type integer but $varType was given.");
        }
    }
}
