<?php

namespace SAML2\Utilities;

use SAML2\Exception\RuntimeException;

/**
 * Simple Array implementation of Collection.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods) - it just has a large api.
 */
class ArrayCollection implements Collection
{
    /**
     * @var array
     */
    protected $elements;

    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    public function add($element)
    {
        $this->elements[] = $element;
    }

    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    public function filter(\Closure $f)
    {
        return new self(array_filter($this->elements, $f));
    }

    public function set($key, $value)
    {
        $this->elements[$key] = $value;
    }

    public function remove($element)
    {
        $key = array_search($element, $this->elements);

        if ($key === false) {
            return false;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    public function getOnlyElement()
    {
        if ($this->count() !== 1) {
            throw new RuntimeException(sprintf(
                __CLASS__ . '::' . __METHOD__ . ' requires that the collection has exactly one element, '
                . '"%d" elements found',
                $this->count()
            ));
        }

        return reset($this->elements);
    }

    public function first()
    {
        return reset($this->elements);
    }

    public function last()
    {
        return end($this->elements);
    }

    public function map(\Closure $function)
    {
        return new self(array_map($function, $this->elements));
    }

    public function count()
    {
        return count($this->elements);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }
}
