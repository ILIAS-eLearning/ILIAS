<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class ilObjFileStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileStakeholder extends AbstractResourceStakeholder
{
    protected $owner = 6;

    /**
     * ilObjFileStakeholder constructor.
     * @param int $owner
     */
    public function __construct(int $owner = 6)
    {
        $this->owner = $owner;
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

}
