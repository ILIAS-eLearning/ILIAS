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

/**
 * Class ilBuddySystemArrayCollection
 * A generic array based collection class
 * @author Michael Jansen <mjansen@databay.de>
 * @template T
 * @template TKey
 * @template-implements ilBuddySystemCollection<TKey,T>
 */
abstract class ilBuddySystemArrayCollection implements ilBuddySystemCollection
{
    /**
     * @param array<string|int, mixed> $elements
     * @phpstan-param array<TKey, T> $elements
     */
    public function __construct(private array $elements = [])
    {
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!isset($offset)) {
            $this->add($value);

            return;
        }

        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function add(mixed $element): void
    {
        $this->elements[] = $element;
    }

    public function remove(string|int $key): void
    {
        if (!$this->containsKey($key)) {
            throw new InvalidArgumentException(sprintf('Could not find an element for key: %s', $key));
        }
        unset($this->elements[$key]);
    }

    public function removeElement(mixed $element): void
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
    public function containsKey(string|int $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    public function getKey($element): string|int
    {
        return array_search($element, $this->elements, true);
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }

    public function get(string|int $key): mixed
    {
        return $this->elements[$key] ?? null;
    }

    public function set(string|int $key, mixed $value): void
    {
        $this->elements[$key] = $value;
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    public function getValues(): array
    {
        return array_values($this->elements);
    }

    public function filter(callable $callable): self
    {
        return new static(array_filter($this->elements, $callable));
    }

    public function slice(int $offset, int $length = null): self
    {
        return new static(array_slice($this->elements, $offset, $length, true));
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function equals(mixed $other): bool
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
