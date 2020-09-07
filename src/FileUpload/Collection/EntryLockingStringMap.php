<?php

namespace ILIAS\FileUpload\Collection;

use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;
use ILIAS\FileUpload\Collection\Exception\NoSuchElementException;
use ILIAS\FileUpload\ScalarTypeCheckAware;

/**
 * Class EntryLockingStringMap
 *
 * Implementation of the StringMap which locks the entry after it got created.
 * Therefore it is not possible to overwrite an existing key.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @Internal
 */
final class EntryLockingStringMap implements StringMap
{
    use ScalarTypeCheckAware;
    /**
     * @var \ArrayObject $map
     */
    private $map;


    /**
     * EntryLockingStringMap constructor.
     */
    public function __construct()
    {
        $this->map = new \ArrayObject();
    }


    /**
     * Returns the value of the given key.
     *
     * @param string $key The key which should be used to search the corresponding value.
     *
     * @return string
     *
     * @throws NoSuchElementException    Thrown if the entry is not found with the given key.
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     *
     * @since 5.3
     */
    public function get($key)
    {
        $this->stringTypeCheck($key, 'key');

        if ($this->map->offsetExists($key)) {
            return $this->map->offsetGet($key);
        }

        throw new NoSuchElementException("No meta data associated with key \"$key\".");
    }


    /**
     * Returns all currently known entries.
     *
     * @return string[]
     *
     * @since 5.3
     */
    public function toArray()
    {
        return $this->map->getArrayCopy();
    }


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
    public function has($key)
    {
        $this->stringTypeCheck($key, 'key');

        return $this->map->offsetExists($key);
    }


    /**
     * Puts a new key value pair into the string array.
     * The put operation can not overwrite an existing pair.
     *
     * @param string $key   The key which should be put into the map.
     * @param string $value The value which should be associated with the given key.
     *
     * @return void
     *
     * @throws ElementAlreadyExistsException    Thrown if the key already exists in the map.
     * @throws \InvalidArgumentException         Thrown if the key or value is not of the type
     *                                           string.
     *
     * @since 5.3
     */
    public function put($key, $value)
    {
        $this->stringTypeCheck($key, 'key');
        $this->stringTypeCheck($value, 'value');

        if ($this->map->offsetExists($key)) {
            throw new ElementAlreadyExistsException("Element $key can not be overwritten.");
        }

        $this->map->offsetSet($key, $value);
    }
}
