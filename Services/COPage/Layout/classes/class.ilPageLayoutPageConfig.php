<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Page layout page configuration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageLayoutPageConfig extends ilPageConfig
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

        $this->setPreventHTMLUnmasking(false);
        $this->setEnableInternalLinks(false);
        $this->setEnablePCType("Question", false);
        $this->setEnablePCType("Map", false);
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("PlaceHolder", true);
    }
}
