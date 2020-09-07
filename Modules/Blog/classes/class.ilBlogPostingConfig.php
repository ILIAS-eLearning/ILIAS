<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Blog posting page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesBlog
 */
class ilBlogPostingConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
    {
        $this->setEnablePCType("Map", true);
        $this->setEnableInternalLinks((bool) $_GET["ref_id"]); // #15668
        $this->setPreventHTMLUnmasking(false);
        $this->setEnableActivation(true);
        
        $blga_set = new ilSetting("blga");
        $this->setPreventHTMLUnmasking(!(bool) $blga_set->get("mask", false));
    }
}
