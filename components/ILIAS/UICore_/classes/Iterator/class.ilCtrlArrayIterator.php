<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlArrayIterator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayIterator implements ilCtrlIteratorInterface
{
    /**
     * @var array
     */
    private array $data;

    /**
     * ilCtrlArrayIterator Constructor
     *
     * @param string[]
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function current(): ?string
    {
        if ($this->valid()) {
            return current($this->data);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @inheritDoc
     */
    public function key(): ?string
    {
        if ($this->valid()) {
            return key($this->data);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        $value = current($this->data);
        $key = key($this->data);

        if (null === $key) {
            return false;
        }

        if (!is_string($value) || !is_string($key)) {
            $this->next();
            return $this->valid();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->data);
    }
}
