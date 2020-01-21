<?php

namespace ILIAS\FileUpload\Collection;

use ILIAS\FileUpload\Collection\Exception\NoSuchElementException;

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
     * @return string
     *
     * @throws NoSuchElementException   Thrown if the entry is not found with the given key.
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     *
     * @since 5.3
     */
    public function get($key);


    /**
     * Returns all currently known entries.
     *
     * @return string[]
     *
     * @since 5.3
     */
    public function toArray();


    /**
     * Probe if the key is known and associated with a value.
     *
     * @param string $key The key which should be checked.
     *
     * @return bool
     *
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     *
     * @since 5.3
     */
    public function has($key);
}
