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

namespace ILIAS\GlobalScreen\UI\Footer\Entries;

class EntryDTO implements Entry
{
    public function __construct(
        private string $id,
        private string $title,
        private bool $active,
        private int $position,
        private string $parent,
        private string $action,
        private bool $external = true,
        private bool $core = false
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function withId(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withActive(bool $active): self
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function withPosition(int $position): self
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function isCore(): bool
    {
        return $this->core;
    }

    public function getParent(): string
    {
        return $this->parent;
    }

    public function withParent(string $parent): self
    {
        $clone = clone $this;
        $clone->parent = $parent;
        return $clone;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function withAction(string $action): Entry
    {
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }

    public function isExternal(): bool
    {
        return $this->external;
    }

    public function withExternal(bool $external): Entry
    {
        $clone = clone $this;
        $clone->external = $external;
        return $clone;
    }

}
