<?php

use ILIAS\ResourceStorage\Resource\Stakeholder\AbstractResourceStakeholder;

/**
 * Class ilMMStorageStakeholder
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMStorageStakeholder extends AbstractResourceStakeholder
{

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return 'mme';
    }
}
