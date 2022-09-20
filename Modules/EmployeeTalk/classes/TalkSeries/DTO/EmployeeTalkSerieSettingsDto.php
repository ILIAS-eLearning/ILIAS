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

/**
 * Class EmployeeTalkSerieSettingsDto
 */
final class EmployeeTalkSerieSettingsDto
{
    /** @var int  $objectId*/
    private int $objectId = -1;
    /** @var bool $lockedEditing */
    private bool $lockedEditing = false;

    /**
     * EmployeeTalk constructor.
     * @param int $objectId
     * @param bool $lockedEditing
     */
    public function __construct(
        int $objectId,
        bool $lockedEditing
    ) {
        $this->objectId = $objectId;
        $this->lockedEditing = $lockedEditing;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     * @return EmployeeTalkSerieSettingsDto
     */
    public function setObjectId(int $objectId): EmployeeTalkSerieSettingsDto
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLockedEditing(): bool
    {
        return $this->lockedEditing;
    }

    /**
     * @param bool $lockedEditing
     * @return EmployeeTalkSerieSettingsDto
     */
    public function setLockedEditing(bool $lockedEditing): EmployeeTalkSerieSettingsDto
    {
        $this->lockedEditing = $lockedEditing;
        return $this;
    }
}
