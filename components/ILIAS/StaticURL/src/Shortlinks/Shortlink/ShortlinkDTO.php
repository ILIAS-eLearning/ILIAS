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

namespace ILIAS\StaticURL\Shortlinks\Shortlink;

use ILIAS\StaticURL\Shortlinks\Shortlink\Target\Type;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\TypeData;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ShortlinkDTO implements Shortlink
{
    public function __construct(
        private string $alias,
        private Type $target_type,
        private TypeData $target_type_data,
        private int $position,
        private bool $active,
        private int $used,
        private ?string $id = null
    ) {
    }

    public function withId(string $id): Shortlink
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withAlias(string $alias): Shortlink
    {
        $clone = clone $this;
        $clone->alias = $alias;
        return $clone;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getAliasForPresentation(string $prefix = ''): string
    {
        return rtrim($prefix, '/') . '/' . trim($this->alias, '/');
    }

    public function withTargetType(): Type
    {
        $clone = clone $this;
        $clone->target_type = $target_type;
        return $clone;
    }

    public function getTargetType(): Type
    {
        return $this->target_type;
    }

    public function withTargetData(TypeData $data): Shortlink
    {
        $clone = clone $this;
        $clone->target_type_data = $data;
        return $clone;
    }

    public function getTargetData(): TypeData
    {
        return $this->target_type_data;
    }

    public function withPosition(int $position): Shortlink
    {
        $clone = clone $this;
        $clone->position = $position;
        return $clone;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function withActive(bool $active): Shortlink
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function increaseUsage(): Shortlink
    {
        $clone = clone $this;
        $clone->used++;
        return $clone;
    }

    public function getUsed(): int
    {
        return $this->used;
    }

}
