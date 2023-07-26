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

/**
 * Class ilCmiXapiAccess
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiAccess
{
    protected ilObjCmiXapi $object;
    protected ilAccessHandler $access;

    /**
     * ilCmiXapiAccess constructor.
     */
    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->object = $object;
        $this->access = $DIC->access();
    }

    public function hasLearningProgressAccess(): bool
    {
        return ilLearningProgressAccess::checkAccess($this->object->getRefId());
    }

    public function hasWriteAccess(?int $usrId = null): bool
    {
        if (isset($usrId)) {
            return $this->access->checkAccessOfUser(
                $usrId,
                'write',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        } else {
            return $this->access->checkAccess(
                'write',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }
    }

    public function hasEditPermissionsAccess(?int $usrId = null): bool
    {
        if (isset($usrId)) {
            return $this->access->checkAccessOfUser(
                $usrId,
                'edit_permission',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        } else {
            return $this->access->checkAccess(
                'edit_permission',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }
    }

    public function hasOutcomesAccess(?int $usrId = null): bool
    {
        if (isset($usrId)) {
            return $this->access->checkAccessOfUser(
                $usrId,
                'read_outcomes',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        } else {
            return $this->access->checkAccess(
                'read_outcomes',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }
    }

    public function hasStatementsAccess(): bool
    {
        if ($this->object->isStatementsReportEnabled()) {
            return true;
        }
        return false;
    }

    public function hasHighscoreAccess(): bool
    {
        if ($this->object->getHighscoreEnabled()) {
            return true;
        }
        return false;
    }

    public static function getInstance(ilObjCmiXapi $object): \ilCmiXapiAccess
    {
        return new self($object);
    }
}
