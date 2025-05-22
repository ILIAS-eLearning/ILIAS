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
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class ilExcSubmissionStakeholder extends AbstractResourceStakeholder
{
    protected ?ilDBInterface $database = null;

    public function getId(): string
    {
        return 'exc_submission';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->default_owner;
    }

    public function canBeAccessedByCurrentUser(ResourceIdentification $identification): bool
    {
        global $DIC;

        $object_id = $this->resolveObjectId($identification);
        if ($object_id === null) {
            return true;
        }

        $ref_ids = ilObject2::_getAllReferences($object_id);
        foreach ($ref_ids as $ref_id) {
            // one must have write permissions on the exercise to see the submission
            if (
                $DIC->access()->checkAccessOfUser($this->current_user, 'read', '', $ref_id) ||
                $DIC->access()->checkAccessOfUser($this->current_user, 'write', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }

    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
    {
        // at this place we could handle de deletion of a resource. not needed for instruction files IMO.

        return true;
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        $this->initDB();
        $object_id = $this->resolveObjectId($identification);
        if ($object_id !== null) {
            $references = ilObject::_getAllReferences($object_id);
            $ref_id = array_shift($references);

            // we currently deliver the goto-url of the exercise in which the resource is used. if possible, you could deliver a more speficic url wo the assignment as well.
            return ilLink::_getLink($ref_id, 'exc');
        }
        return null;
    }

    private function resolveObjectId(ResourceIdentification $identification): ?int
    {
        $this->initDB();
        $r = $this->database->queryF(
            "SELECT obj_id FROM exc_returned WHERE exc_returned.solution_rid = %s;",
            ['text'],
            [$identification->serialize()]
        );
        $d = $this->database->fetchObject($r);

        return (isset($d->obj_id) ? (int) $d->obj_id : null);
    }

    private function initDB(): void
    {
        global $DIC;
        if ($this->database === null) {
            $this->database = $DIC->database();
        }
    }
}
