<?php

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class ilSkillProfileStorageStakeHolder
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillProfileStorageStakeHolder extends AbstractResourceStakeholder
{
    protected int $owner = 6;

    public function __construct(int $owner = 6)
    {
        $this->owner = $owner;
    }

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return 'skl_prof';
    }

    /**
     * @inheritDoc
     */
    public function getOwnerOfNewResources() : int
    {
        return $this->owner;
    }
}
