<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;

/**
 * ilScriptActivityTest is part of the workflow engine.
 *
 * This class holds all tests for the class activities/class.ilScriptActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilScriptActivityTest extends PHPUnit_Framework_TestCase
{
    /** vfsStream Test Directory, see setup. */
    public $test_dir;

    public function setUp()
    {
        chdir(dirname(__FILE__));
        chdir('../../../../');

        try {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            //ilUnitUtil::performInitialisation();
        } catch (Exception $exception) {
            if (!defined('IL_PHPUNIT_TEST')) {
                define('IL_PHPUNIT_TEST', false);
            }
        }

        // Empty workflow.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
        $this->workflow = new ilEmptyWorkflow();
        
        // Basic node
        require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
        $this->node = new ilBasicNode($this->workflow);
        
        // Wiring up so the node is attached to the workflow.
        $this->workflow->addNode($this->node);
                
        require_once './Services/WorkflowEngine/classes/activities/class.ilScriptActivity.php';

        $this->test_dir = vfs\vfsStream::setup('example');
    }

    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting != null) {
            //$ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            //$ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext()
    {
        // Act
        $activity = new ilScriptActivity($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testGetContext()
    {
        // Arrange
        $activity = new ilScriptActivity($this->node);

        // Act
        $actual = $activity->getContext();

        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }

    public function testSetGetMethod()
    {
        // Arrange
        $activity = new ilScriptActivity($this->node);

        $activity->setMethod(function () {
            return 'Hallo, Welt!';
        });

        // Act
        $response = $activity->getScript();

        // Assert
        $this->assertEquals($response(), "Hallo, Welt!");
    }
}
