<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Imprint page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilImprintConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init() : void
    {
        $this->setPreventHTMLUnmasking(true);
        $this->setEnableInternalLinks(false);
        $this->setEnableWikiLinks(false);
        $this->setEnableActivation(true);
    }
}
