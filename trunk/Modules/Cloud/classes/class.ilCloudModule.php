<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilModule.php");

/**
 * Class ilCloudModule
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:
 *
 * @ingroup ModulesCloud
 */

class ilCloudModule extends ilModule
{

    /**
     * Constructor: read information on component
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Core modules vs. plugged in modules
     */
    function isCore()
    {
        return true;
    }

    /**
     * Get version of module. This is especially important for
     * non-core modules.
     */
    function getVersion()
    {
        return "-";
    }

}

?>