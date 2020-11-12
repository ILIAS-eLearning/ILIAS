<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class ilObjFileStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileStakeholder extends AbstractResourceStakeholder
{
    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return 'file_obj';
    }

}
