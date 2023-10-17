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

class ilMDSettingsAccessService
{
    protected const VISIBLE = 'visible';
    protected const READ = 'read';
    protected const WRITE = 'write';
    protected const EDIT_PERMISSION = 'edit_permission';

    protected ilAccess $access;
    protected int $ref_id;

    public function __construct(int $ref_id, ilAccess $access)
    {
        $this->ref_id = $ref_id;
        $this->access = $access;
    }

    public function hasCurrentUserVisibleAccess(): bool
    {
        return $this->hasCurrentUserAccess(self::VISIBLE);
    }

    public function hasCurrentUserReadAccess(): bool
    {
        return $this->hasCurrentUserAccess(self::READ);
    }

    public function hasCurrentUserWriteAccess(): bool
    {
        return $this->hasCurrentUserAccess(self::WRITE);
    }

    public function hasCurrentUserPermissionsAccess(): bool
    {
        return $this->hasCurrentUserAccess(self::EDIT_PERMISSION);
    }

    protected function hasCurrentUserAccess(string $permission): bool
    {
        return $this->access->checkAccess($permission, '', $this->ref_id);
    }
}
