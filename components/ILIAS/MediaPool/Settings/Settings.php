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

namespace ILIAS\MediaPool\Settings;

class Settings
{
    public function __construct(
        protected int $id,
        protected int $default_width,
        protected int $default_height,
        protected bool $for_translation
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDefaultWidth(): int
    {
        return $this->default_width;
    }

    public function getDefaultHeight(): int
    {
        return $this->default_height;
    }

    public function getForTranslation(): bool
    {
        return $this->for_translation;
    }
}
