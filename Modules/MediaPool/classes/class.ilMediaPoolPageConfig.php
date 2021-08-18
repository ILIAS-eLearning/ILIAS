<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Media pool page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaPoolPageConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
    {
        $this->setEnableInternalLinks(false);
        $this->setPreventHTMLUnmasking(false);
        $this->setMultiLangSupport(true);
    }
}
