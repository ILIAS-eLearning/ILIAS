<?php

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

declare(strict_types=1);

namespace ILIAS\KioskMode;

/**
 * Keeps the state of a view in a simple stringly type key-value store.
 */
class State
{
    /**
     * @var array <string, string>
     */
    protected ?array $store = null;

    /**
     * Set a value for a key of the state.
     */
    public function withValueFor(string $key, string $value): State
    {
        $clone = clone $this;
        $clone->store[$key] = $value;
        return $clone;
    }

    /**
     * Remove the key-value-pair.
     */
    public function withoutKey(string $key): State
    {
        $clone = clone $this;
        unset($clone->store[$key]);
        return $clone;
    }

    /**
     * Get the value for the given key.
     */
    public function getValueFor(string $key): ?string
    {
        if (!$this->store) {
            return null;
        }
        return $this->store[$key];
    }

    /**
     * Get the key-value store as string
     */
    public function serialize(): string
    {
        return json_encode($this->store, JSON_THROW_ON_ERROR);
    }
}
