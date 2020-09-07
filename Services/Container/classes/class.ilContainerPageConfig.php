<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Container page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesContainer
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

        $mset = new ilSetting("mobs");
        if ($mset->get("mep_activate_pages")) {
            $this->setEnablePCType("ContentInclude", true);
        }
    }
}
