<?php

namespace ILIAS\FileUpload\Collection;

/**
 * Class StringMap
 *
 * This interface provides the standard interface for the mutable string map implementation.
 * Maps in general are collections which map a key to value.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
interface StringMap extends ImmutableStringMap
{

    /**
     * Puts a new key value pair into the string array.
     *
     * @param string $key   The key which should be put into the map.
     * @param string $value The value which should be associated with the given key.
     *
     * @return void
     *
     * @throws \InvalidArgumentException         Thrown if the key or value is not of the type
     *                                          string.
     *
     * @since 5.3
     */
    public function put($key, $value);
}
