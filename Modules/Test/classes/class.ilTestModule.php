<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilModule.php';

/**
 * Test Module.
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilTestModule extends ilModule
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
        return '-';
    }
}
