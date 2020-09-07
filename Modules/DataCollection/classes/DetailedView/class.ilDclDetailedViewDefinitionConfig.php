<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * View definition page configuration
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesDataCollection
 */
class ilDclDetailedViewDefinitionConfig extends ilPageConfig
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
