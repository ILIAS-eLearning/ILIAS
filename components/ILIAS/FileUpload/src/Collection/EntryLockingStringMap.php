<?php

namespace ILIAS\FileUpload\Collection;

use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;
use ILIAS\FileUpload\Collection\Exception\NoSuchElementException;
use ILIAS\FileUpload\ScalarTypeCheckAware;

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
    private \ArrayObject $map;


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
     * @throws NoSuchElementException    Thrown if the entry is not found with the given key.
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     * @since 5.3
     */
    public function get(string $key): string
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
    public function toArray(): array
    {
        return $this->map->getArrayCopy();
    }


    /**
     * Probe if the key is known and associated with a value.
     *
     * @param string $key The key which should be checked.
     *
     * @throws \InvalidArgumentException Thrown if the key type is not of the type string.
     * @since 5.3
     */
    public function has(string $key): bool
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
     * @throws ElementAlreadyExistsException    Thrown if the key already exists in the map.
     * @throws \InvalidArgumentException         Thrown if the key or value is not of the type
     *                                           string.
     * @since 5.3
     */
    public function put(string $key, string $value): void
    {
        $this->stringTypeCheck($key, 'key');
        $this->stringTypeCheck($value, 'value');

        if ($this->map->offsetExists($key)) {
            throw new ElementAlreadyExistsException("Element $key can not be overwritten.");
        }

        $this->map->offsetSet($key, $value);
    }
}
