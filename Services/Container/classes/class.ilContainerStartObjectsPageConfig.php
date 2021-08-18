<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Container start objects page configuration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilContainerStartObjectsPageConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
    {
        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType("RepositoryItem");
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("Map", true);
        $this->setEnablePCType("Resources", false);
        $this->setMultiLangSupport(false);
        $this->setSinglePageMode(true);
    }
}
