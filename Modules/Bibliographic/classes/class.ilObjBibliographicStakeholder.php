<?php

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class ilObjBibliographicStakeholder
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilObjBibliographicStakeholder extends AbstractResourceStakeholder
{
    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return 'bibl';
    }

    /**
     * @inheritDoc
     */
    public function getOwnerOfNewResources() : int
    {
        return 6;
    }
}
