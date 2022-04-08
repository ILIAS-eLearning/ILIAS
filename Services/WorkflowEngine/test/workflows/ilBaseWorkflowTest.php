<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

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
class ilBaseWorkflowTest extends TestCase
{
    protected function setUp() : void
    {
    }
    
    protected function tearDown() : void
    {
        global $ilSetting;
        if ($ilSetting != null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
}
