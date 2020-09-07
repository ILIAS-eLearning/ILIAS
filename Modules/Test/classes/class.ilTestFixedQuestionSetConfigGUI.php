<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class that manages the question set configuration for tests
 * requireing a once defined question set
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestFixedQuestionSetConfigGUI: ilTestExpressPageObjectGUI
 * @ilCtrl_Calls ilTestFixedQuestionSetConfigGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilTestFixedQuestionSetConfigGUI: ilAssQuestionPageGUI
 */
class ilTestFixedQuestionSetConfigGUI
{
    /**
     * @var ilCtrl
     */
    public $ctrl = null;
    
    /**
     * @var ilAccess
     */
    public $access = null;
    
    /**
     * @var ilTabsGUI
     */
    public $tabs = null;
    
    /**
     * @var ilLanguage
     */
    public $lng = null;
    
    /**
     * @var ilTemplate
     */
    public $tpl = null;
    
    /**
     * @var ilDBInterface
     */
    public $db = null;
    
    /**
     * @var ilTree
     */
    public $tree = null;
    
    /**
     * @var ilPluginAdmin
     */
    public $pluginAdmin = null;
    
    /**
     * @var ilObjectDefinition
     */
    public $objDefinition = null;
    
    /**
     * @var ilObjTest
     */
    public $testOBJ = null;
    
    /**
     * ilTestFixedQuestionSetConfigGUI constructor.
     */
    public function __construct()
    {
    }
    /**
     * Control Flow Entrance
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ctrl = $DIC['ilCtrl']; /* @var ilCtrl $ctrl */
        //switch( $ctrl->getNextClass() )
    }
}
