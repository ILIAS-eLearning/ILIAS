<?php declare(strict_types=1);

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

/**
 * Class ilBuddySystemArrayCollection
 * A collection which contains all entries of a buddy list
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemArrayCollection implements ilBuddySystemCollection
{
    private array $elements;

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
    public function offsetExists($offset) : bool
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
    public function offsetSet($offset, $value) : void
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
    public function offsetUnset($offset) : void
    {
        $this->remove($offset);
    }

    /**
     * @inheritDoc
     */
    public function count() : int
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
        if (!$this->containsKey($key)) {
            throw new InvalidArgumentException(sprintf('Could not find an element for key: %s', $key));
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
            throw new InvalidArgumentException('Could not find an key for the passed element.');
        }
        unset($this->elements[$key]);
    }

    /**
     * isset is used for performance reasons (array_key_exists is much slower).
     * array_key_exists is only used in case of a null value (see https://www.php.net/manual/en/function.array-key-exists.php Example #2 array_key_exists() vs isset()).
     *
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
        return $this->elements[$key] ?? null;
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
    public function filter(callable $callable) : ilBuddySystemCollection
    {
        return new static(array_filter($this->elements, $callable));
    }

    /**
     * @inheritDoc
     */
    public function slice(int $offset, int $length = null) : ilBuddySystemCollection
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

    public function equals($other) : bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        $self = $this->toArray();
        $other = $other->toArray();

        sort($self);
        sort($other);

        return $self == $other;
    }
}
