<?php

namespace ILIAS\FileUpload\Collection;

use ILIAS\FileUpload\Collection\Exception\NoSuchElementException;

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
 * Class ImmutableStringMap
 *
 * This interface provides the standard interface for the immutable string map implementation.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
interface ImmutableStringMap
{

    /**
     * Returns the value of the key from the additional metadata.
     *
     * @param string $key The key which should be used to search the corresponding meta data value.
     *
     *
     * @throws NoSuchElementException   Thrown if the entry is not found with the given key.
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     * @since 5.3
     */
    public function get(string $key) : string;


    /**
     * Returns all currently known entries.
     *
     * @return string[]
     *
     * @since 5.3
     */
    public function toArray() : array;


    /**
     * Probe if the key is known and associated with a value.
     *
     * @param string $key The key which should be checked.
     *
     *
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     * @since 5.3
     */
    public function has(string $key) : bool;
}
