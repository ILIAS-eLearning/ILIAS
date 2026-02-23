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

namespace ILIAS\StaticURL\Shortlinks\Shortlink\Target;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
#[\AllowDynamicProperties]
class TypeData extends \ArrayObject
{
    public function serialize(): string
    {
        return $this->__serialize()['data'];
    }

    public function unserialize(string $data): void
    {
        $this->__unserialize(['data' => $data]);
    }

    /**
     * @internal
     */
    public function __serialize(): array
    {
        return ['data' => json_encode($this, JSON_THROW_ON_ERROR)];
    }

    /**
     * @internal
     */
    public function __unserialize(array $data): void
    {
        foreach ((array) (json_decode($data['data'] ?? '[]', true)) as $k => $item) {
            $this->$k = $item;
            $this[$k] = $item;
        }
    }

    public function with(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->$key = $value;
        $clone[$key] = $value;
        return $clone;
    }

}
