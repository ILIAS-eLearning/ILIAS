<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary definition page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilGlossaryDefPageConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
    {
        $this->setEnableKeywords(true);
        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType("GlossaryItem");
        $this->setIntLinkHelpDefaultId($_GET["ref_id"]);
    }
}
