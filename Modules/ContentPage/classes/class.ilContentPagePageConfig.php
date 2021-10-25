<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPagePageConfig
 */
class ilContentPagePageConfig extends ilPageConfig
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
        $this->setMultiLangSupport(true);
        $this->setUsePageContainer(false);

        $mediaPoolSettings = new ilSetting('mobs');
        if ($mediaPoolSettings->get('mep_activate_pages', '0')) {
            $this->setEnablePCType('ContentInclude', true);
        }
    }
}
