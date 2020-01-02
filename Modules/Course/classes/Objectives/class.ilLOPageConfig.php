<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * (Course) learning objective page configuration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesCourse
 */
class ilLOPageConfig extends ilPageConfig
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
