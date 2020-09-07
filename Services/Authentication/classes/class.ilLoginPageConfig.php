<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Login page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAuthentication
 */
class ilLoginPageConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
    {
        $this->setEnablePCType("LoginPageElement", true);
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("Map", false);
        $this->setEnableInternalLinks(true);
    }
}
