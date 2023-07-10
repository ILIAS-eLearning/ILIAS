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

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileStakeholder extends AbstractResourceStakeholder
{
    protected int $owner = 6;
    private int $current_user;
    protected ?ilDBInterface $database = null;

    /**
     * ilObjFileStakeholder constructor.
     */
    public function __construct(int $owner = 6)
    {
        global $DIC;
        $this->current_user = (int)($DIC->isDependencyAvailable('user') ? $DIC->user()->getId() : ANONYMOUS_USER_ID);
        $this->owner = $owner;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'file_obj';
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
            if ($DIC->access()->checkAccessOfUser($this->current_user, 'read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }

    private function resolveObjectId(ResourceIdentification $identification): ?int
    {
        $this->initDB();
        $r = $this->database->queryF(
            "SELECT file_id FROM file_data WHERE rid = %s",
            ['text'],
            [$identification->serialize()]
        );
        $d = $this->database->fetchObject($r);

        return (isset($d->file_id) ? (int)$d->file_id : null);
    }


    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
    {
        $object_id = $this->resolveObjectId($identification);
        try {
            $this->database->manipulateF(
                "UPDATE object_data SET offline = 1 WHERE obj_id = %s",
                ['text'],
                [$object_id]
            );
        } catch (Throwable $t) {
            return false;
        }
        return true;
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        $this->initDB();
        $r = $this->database->queryF(
            "SELECT file_id FROM file_data WHERE rid = %s",
            ['text'],
            [$identification->serialize()]
        );
        $d = $this->database->fetchObject($r);
        if (property_exists($d, 'file_id') && $d->file_id !== null) {
            $references = ilObject::_getAllReferences($d->file_id);
            $ref_id = array_shift($references);

            return ilLink::_getLink($ref_id, 'file');
        }
        return null;
    }

    private function initDB(): void
    {
        global $DIC;
        $this->database = $DIC->database();
    }
}
