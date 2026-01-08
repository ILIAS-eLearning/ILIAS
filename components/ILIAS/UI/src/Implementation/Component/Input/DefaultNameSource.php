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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Generates enumerated input names like 'input_0' or 'input_0/input_0'.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DefaultNameSource implements NameSource
{
    protected const NAME_DELIMITER = '/';
    protected const DEFAULT_NAME = 'input_';

    /** @var array<string, bool> */
    protected array $used_input_name_lookup_table = [];
    protected ?string $parent_name = null;
    protected int $count = 0;

    public function getNextName(?string $dedicated_name = null): string
    {
        $new_name = $dedicated_name ?? (self::DEFAULT_NAME . $this->count++);
        if (null !== $this->parent_name) {
            $new_name = $this->parent_name . self::NAME_DELIMITER . $new_name;
        }
        if (isset($this->used_input_name_lookup_table[$new_name])) {
            throw new \LogicException("Input name '$new_name' was already generated.");
        }
        $this->used_input_name_lookup_table[$new_name] = true;
        return $new_name;
    }

    public function withParentName(string $parent_name): static
    {
        $clone = clone $this;
        $clone->parent_name = $parent_name;
        $clone->count = 0;
        return $clone;
    }

    public function withReset(): static
    {
        $clone = clone $this;
        $clone->used_input_name_lookup_table = [];
        $clone->parent_name = null;
        $clone->count = 0;
        return $clone;
    }
}
