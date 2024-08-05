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

use ILIAS\Exercise\InternalService;
use ILIAS\Exercise;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

class ilExcSampleSolutionStakeholder extends AbstractResourceStakeholder
{
    protected int $owner = 6;
    private int $current_user;
    protected ?ilDBInterface $database = null;

    public function __construct(int $owner = 6)
    {
        global $DIC;
        $this->current_user = (int) ($DIC->isDependencyAvailable('user')
            ? $DIC->user()->getId()
            : (defined('ANONYMOUS_USER_ID') ? ANONYMOUS_USER_ID : 6));
        $this->owner = $owner;
    }

    public function getId(): string
    {
        return 'exc_sample_solution';
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->owner;
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
            // one must have read permissions on the exercise to see the instruction files
            if ($DIC->access()->checkAccessOfUser($this->current_user, 'read', '', $ref_id)) {
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
            "SELECT exc_id FROM exc_assignment WHERE exc_assignment.solution_rid = %s;",
            ['text'],
            [$identification->serialize()]
        );
        $d = $this->database->fetchObject($r);

        return (isset($d->exc_id) ? (int) $d->exc_id : null);
    }

    private function initDB(): void
    {
        global $DIC;
        if ($this->database === null) {
            $this->database = $DIC->database();
        }
    }
}
