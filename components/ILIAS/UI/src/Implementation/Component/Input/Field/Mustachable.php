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

namespace ILIAS\UI\Implementation\Component\Input\Field;

/**
 * Trait for components implementing Mustachable providing standard
 * implementation.
 */
trait Mustachable
{
    protected $mustachable = false;
    protected array $placeholder_entries = [];
    protected string $placeholder_advice = '';

    public function withMustachable(?array $placeholders): self
    {
        $clone = $this;
        $clone->mustachable = true;
        if ($placeholders !== null) {
            $clone->placeholder_entries = $placeholders;
        }
        return $clone;
    }

    public function isMustachable(): bool
    {
        return $this->mustachable;
    }

    public function getPlaceholderEntries(): array
    {
        return $this->placeholder_entries;
    }

    public function withPlaceholderAdvice(string $text): self
    {
        $clone = clone $this;
        $clone->placeholder_advice = $text;
        return $clone;
    }

    public function getPlaceholderAdvice(): string
    {
        return $this->placeholder_advice;
    }
}
