<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPagePageConfig
 */
class ilContentPagePageConfig extends \ilPageConfig
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType('RepositoryItem');
        $this->setSinglePageMode(true);
        $this->setEnablePermissionChecks(true);
    }
}