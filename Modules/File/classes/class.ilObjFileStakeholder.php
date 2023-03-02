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
    protected ?ilDBInterface $database = null;

    /**
     * ilObjFileStakeholder constructor.
     */
    public function __construct(int $owner = 6)
    {
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

    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
    {
        $this->initDB();
        $r = $this->database->queryF(
            "SELECT file_id FROM file_data WHERE rid = %s",
            ['text'],
            [$identification->serialize()]
        );
        $d = $this->database->fetchObject($r);
        if (property_exists($d, 'file_id') && $d->file_id !== null) {
            try {
                $this->database->manipulateF(
                    "UPDATE object_data SET offline = 1 WHERE obj_id = %s",
                    ['text'],
                    [$d->file_id]
                );
            } catch (Throwable $t) {
                return false;
            }
            return true;
        }
        return false;
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
