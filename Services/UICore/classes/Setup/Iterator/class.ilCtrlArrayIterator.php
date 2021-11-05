<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlArrayIterator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlArrayIterator implements ilCtrlIteratorInterface
{
    /**
     * @var array
     */
    private array $data;

    /**
     * ilCtrlArrayIterator Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function current() : string
    {
        return current($this->data);
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        next($this->data);
    }

    /**
     * @inheritDoc
     */
    public function key() : string
    {
        return key($this->data);
    }

    /**
     * @inheritDoc
     */
    public function valid() : bool
    {
        $key = key($this->data);
        if (null === $key) {
            return false;
        }

        if (!is_string($key) && !is_string($this->current())) {
            $this->next();
            return $this->valid();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rewind() : void
    {
        reset($this->data);
    }
}