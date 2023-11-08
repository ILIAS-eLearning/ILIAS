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

namespace ILIAS\MetaData\Copyright;

class Entry implements EntryInterface
{
    protected int $id;
    protected string $title;
    protected string $description;
    protected bool $is_default;
    protected bool $is_outdated;
    protected int $position;
    protected CopyrightDataInterface $data;

    public function __construct(
        int $id,
        string $title,
        string $description,
        bool $is_default,
        bool $is_outdated,
        int $position,
        CopyrightDataInterface $data
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->is_default = $is_default;
        $this->is_outdated = $is_outdated;
        $this->position = $position;
        $this->data = $data;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function isOutdated(): bool
    {
        return $this->is_outdated;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function copyrightData(): CopyrightDataInterface
    {
        return $this->data;
    }
}
