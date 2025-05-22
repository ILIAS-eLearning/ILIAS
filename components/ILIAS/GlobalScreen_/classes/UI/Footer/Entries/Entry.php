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

use ILIAS\GlobalScreen\UI\Footer\Translation\TranslatableItem;

interface Entry extends TranslatableItem
{
    public function getId(): string;

    public function withId(string $id): self;

    public function getTitle(): string;

    public function withTitle(string $title): self;

    public function withActive(bool $active): self;

    public function isActive(): bool;

    public function withPosition(int $position): self;

    public function getPosition(): int;

    public function isCore(): bool;

    public function getParent(): string;

    public function withParent(string $parent): self;

    public function getAction(): string;

    public function withAction(string $action): self;

    public function isExternal(): bool;

    public function withExternal(bool $external): self;
}
