<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Container page configuration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerPageConfig extends ilPageConfig
{
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * Init
     */
    public function init()
    {
        global $DIC;

        $this->settings = $DIC->settings();

        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType("RepositoryItem");
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("Map", true);
        $this->setEnablePCType("Resources", true);
        $this->setMultiLangSupport(true);
        $this->setSinglePageMode(true);
        $this->setEnablePermissionChecks(true);
        $this->setUsePageContainer(false);

        $mset = new ilSetting("mobs");
        if ($mset->get("mep_activate_pages")) {
            $this->setEnablePCType("ContentInclude", true);
        }
    }
}
