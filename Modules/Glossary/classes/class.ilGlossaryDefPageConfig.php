<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Glossary definition page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
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
