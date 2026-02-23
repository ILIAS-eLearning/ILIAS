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

namespace ILIAS\Help\GuidedTour\Settings;

class Settings
{
    public function __construct(
        protected int $obj_id,
        protected bool $active,
        protected string $screen_ids,
        protected PermissionType $permission,
        protected string $lang
    ) {
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getScreenIds(): string
    {
        return $this->screen_ids;
    }

    public function getPermission(): PermissionType
    {
        return $this->permission;
    }

    public function getLanguage(): string
    {
        return $this->lang;
    }
}
