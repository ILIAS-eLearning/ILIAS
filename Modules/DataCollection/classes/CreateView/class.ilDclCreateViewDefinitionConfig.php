<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclCreateViewDefinitionConfig
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilDclCreateViewDefinitionConfig extends ilPageConfig
{

    /**
     * Init
     */
    public function init()
    {
        // config
        $this->setPreventHTMLUnmasking(true);
        $this->setEnableInternalLinks(false);
        $this->setEnableWikiLinks(false);
        $this->setEnableActivation(false);
    }
}
