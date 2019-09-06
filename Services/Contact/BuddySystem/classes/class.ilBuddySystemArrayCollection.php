<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemArrayCollection
 * A collection which contains all entries of a buddy list
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemArrayCollection implements ilBuddySystemCollection
{
    /** @var array */
    private $elements = [];

    /**
     * ilBuddySystemArrayCollection constructor.
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->add($value);
            return;
        }

        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function add($element) : void
    {
        $this->elements[] = $element;
    }

    /**
     * @inheritDoc
     */
    public function remove($key) : void
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            throw new InvalidArgumentException(sprintf("Could not find an element for key: %s", $key));
        }
        unset($this->elements[$key]);
    }

    /**
     * @inheritDoc
     */
    public function removeElement($element) : void
    {
        $key = array_search($element, $this->elements, true);
        if (false === $key) {
            throw new InvalidArgumentException("Could not find an key for the passed element.");
        }
        unset($this->elements[$key]);
    }

    /**
     * @inheritDoc
     */
    public function containsKey($key) : bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * @inheritDoc
     */
    public function getKey($element)
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * @inheritDoc
     */
    public function clear() : void
    {
        $this->elements = [];
    }

    /**
     * @inheritDoc
     */
    public function contains($element) : bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value) : void
    {
        $this->elements[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty() : bool
    {
        return empty($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function getKeys() : array
    {
        return array_keys($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function getValues() : array
    {
        return array_values($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function filter(callable $callable)
    {
        return new static(array_filter($this->elements, $callable));
    }

    /**
     * @inheritDoc
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->elements, $offset, $length, true));
    }

    /**
     * @inheritDoc
     */
    public function toArray() : array
    {
        return $this->elements;
    }
}