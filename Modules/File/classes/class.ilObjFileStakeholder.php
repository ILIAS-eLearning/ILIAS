<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilObjFileStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileStakeholder extends AbstractResourceStakeholder
{
    protected $owner = 6;
    /**
     * @var ilDBInterface
     */
    protected $database;

    /**
     * ilObjFileStakeholder constructor.
     * @param int $owner
     */
    public function __construct(int $owner = 6)
    {
        global $DIC;
        $this->owner = $owner;
        $this->database = $DIC->database();
    }

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return 'file_obj';
    }

    public function getOwnerOfNewResources() : int
    {
        return $this->owner;
    }

    public function resourceHasBeenDeleted(ResourceIdentification $identification) : bool
    {
        $r = $this->database->queryF("SELECT file_id FROM file_data WHERE rid = %s", ['text'],
            [$identification->serialize()]);
        $d = $this->database->fetchObject($r);
        if (isset($d->file_id)) {
            try {
                $this->database->manipulateF("UPDATE object_data SET offline = 1 WHERE obj_id = %s", ['text'],
                    [$d->file_id]);
            } catch (Throwable $t) {
                return false;
            }
            return true;
        }
        return false;
    }

}
