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

namespace ILIAS\Mail\Autoresponder;

use InvalidArgumentException;
use ArrayIterator;

final class AutoresponderArrayCollection implements AutoresponderCollection
{
    /** @var AutoresponderDto[] */
    private $elements;

    /**
     * @param AutoresponderDto[]|array<int|string, AutoresponderDto> $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function offsetExists(int $offset) : bool
    {
        return $this->containsKey($offset);
    }

    public function offsetGet(int $offset) : AutoresponderDto
    {
        return $this->get($offset);
    }

    public function offsetSet(int $offset, AutoresponderDto $value) : void
    {
        if (!isset($offset)) {
            $this->add($value);

            return;
        }

        $this->set($offset, $value);
    }

    public function offsetUnset(int $offset) : void
    {
        $this->remove($offset);
    }

    public function count() : int
    {
        return count($this->elements);
    }

    public function add(AutoresponderDto $element) : void
    {
        $this->elements[] = $element;
    }

    public function remove($key) : void
    {
        if (!$this->containsKey($key)) {
            throw new InvalidArgumentException("Key $key does not exist.");
        }
        unset($this->elements[$key]);
    }

    public function removeElement(AutoresponderDto $element) : void
    {
        $key = array_search($element, $this->elements, true);
        if (false === $key) {
            throw new InvalidArgumentException('Could not find an key for the passed element.');
        }
        unset($this->elements[$key]);
    }

    public function containsKey($key) : bool
    {
        return isset($this->elements[$key]);
    }

    public function getKey(AutoresponderDto $element) : int
    {
        $key = array_search($element, $this->elements, true);
        if (false === $key) {
            throw new InvalidArgumentException('Could not find an key for the passed element.');
        }
        return $key;
    }

    public function clear() : void
    {
        $this->elements = [];
    }

    public function contains(AutoresponderDto $element) : bool
    {
        return in_array($element, $this->elements, true);
    }

    public function get($key) : ?AutoresponderDto
    {
        return $this->elements[$key] ?? null;
    }

    public function set($key, AutoresponderDto $value) : void
    {
        $this->elements[$key] = $value;
    }

    public function isEmpty() : bool
    {
        return empty($this->elements);
    }

    public function getKeys() : array
    {
        return array_keys($this->elements);
    }

    public function getValues() : array
    {
        return array_values($this->elements);
    }

    public function filter(callable $callable) : AutoresponderCollection
    {
        $filtered = array_filter($this->elements, $callable);
        return new self($filtered);
    }

    public function slice(int $offset, int $length = null) : AutoresponderCollection
    {
        $sliced = array_slice($this->elements, $offset, $length, true);
        return new self($sliced);
    }

    public function toArray() : array
    {
        return $this->elements;
    }

    public function equals($other) : bool
    {
        if (!$other instanceof self) {
            return false;
        }

        $self = $this->toArray();
        $other = $other->toArray();

        sort($self);
        sort($other);

        return $self === $other;
    }
}
