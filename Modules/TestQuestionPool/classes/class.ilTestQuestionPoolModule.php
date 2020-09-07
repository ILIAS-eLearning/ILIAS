<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilModule.php");

/**
* TestQuestionPool Module.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesTestQuestionPool
*/
class ilTestQuestionPoolModule extends ilModule
{
    
    /**
    * Constructor: read information on component
    */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
    * Core modules vs. plugged in modules
    */
    public function isCore()
    {
        return true;
    }

    /**
    * Get version of module. This is especially important for
    * non-core modules.
    */
    public function getVersion()
    {
        return "-";
    }
}
