<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

/**
 * Keeps the state of a view in a simple stringly type key-value store.
 */
class State
{

    /**
     * @var array <string, string>
     */
    protected $store;

    /**
     * Set a value for a key of the state.
     */
    public function withValueFor(string $key, string $value) : State
    {
        $clone = clone $this;
        $clone->store[$key] = $value;
        return $clone;
    }

    /**
     * Remove the key-value-pair.
     */
    public function withoutKey(string $key) : State
    {
        $clone = clone $this;
        unset($clone->store[$key]);
        return $clone;
    }

    /**
     * Get the value for the given key.
     */
    public function getValueFor(string $key) : string
    {
        return $this->store[$key];
    }

    /**
     * Get the key-value store as string
     */
    public function serialize() : string
    {
        return json_encode($this->store);
    }
}
