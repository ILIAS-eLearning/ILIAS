<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilBaseWorkflowTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * workflows/class.BaseWorkflow
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilBaseWorkflowTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        //ilUnitUtil::performInitialisation();
        
        // Empty workflow as test fixture for the abstract class.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
    }
    
    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting != null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
}
