<?php

declare(strict_types=1);

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

namespace ILIAS\Modules\EmployeeTalk\TalkSeries\DTO;

final class EmployeeTalkSerieSettingsDto
{
    private int $obj_id = -1;
    private bool $locked_editing = false;

    public function __construct(
        int $obj_id,
        bool $locked_editing
    ) {
        $this->obj_id = $obj_id;
        $this->locked_editing = $locked_editing;
    }

    public function getObjectId(): int
    {
        return $this->obj_id;
    }

    public function setObjectId(int $obj_id): EmployeeTalkSerieSettingsDto
    {
        $this->obj_id = $obj_id;
        return $this;
    }

    public function isLockedEditing(): bool
    {
        return $this->locked_editing;
    }

    public function setLockedEditing(bool $locked_editing): EmployeeTalkSerieSettingsDto
    {
        $this->locked_editing = $locked_editing;
        return $this;
    }
}
