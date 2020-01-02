<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/WorkflowEngine/test/ilWorkflowEngineBaseTest.php';

/**
 * ilSettingActivityTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class activities/class.ilSettingActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSettingActivityTest extends ilWorkflowEngineBaseTest
{
    public function setUp()
    {
        parent::setUp();

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        //ilUnitUtil::performInitialisation();
        
        // Empty workflow.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
        $this->workflow = new ilEmptyWorkflow();
        
        // Basic node
        require_once './Services/WorkflowEngine/classes/nodes/class.ilBasicNode.php';
        $this->node = new ilBasicNode($this->workflow);
        
        // Wiring up so the node is attached to the workflow.
        $this->workflow->addNode($this->node);
        
        require_once './Services/WorkflowEngine/classes/activities/class.ilSettingActivity.php';
    }
    
    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting != null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext()
    {
        // Act
        $activity = new ilSettingActivity($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetSettingName()
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);

        // Act
        $expected = 'Günther';
        $activity->setSettingName($expected);
        $actual = $activity->getSettingName();

        // Assert
        $this->assertEquals($actual, $expected);
    }
    
    public function testSetGetSettingValue()
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);

        // Act
        $expected = 'Günther';
        $activity->setSettingValue($expected);
        $actual = $activity->getSettingValue();

        // Assert
        $this->assertEquals($actual, $expected);
    }
    
    public function testSetSetting()
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);
        
        // Act
        $expected_name = 'Ralle';
        $expected_value  = 'OK';
        $activity->setSetting($expected_name, $expected_value);
        $actual_name = $activity->getSettingName();
        $actual_value = $activity->getSettingValue();
        
        // Assert
        $this->assertEquals(
            $actual_name . $actual_value,
            $expected_name . $expected_value
        );
    }
    
    public function testExecute()
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);
        $expected_name = 'Ralle';
        $expected_val  = 'OK';
        $activity->setSetting($expected_name, $expected_val);

        require_once './Services/Administration/classes/class.ilSetting.php';
        $ilSetting_mock = $this->createMock('ilSetting', array('set'), array(), '', false);

        $ilSetting_mock->expects($this->exactly(1))
                       ->method('set')
                       ->with($expected_name, $expected_val);
        $stashed_real_object = @$GLOBALS['ilSetting'];
        $GLOBALS['ilSetting'] = $ilSetting_mock;

        // Act
        $activity->execute();
        
        $GLOBALS['ilSetting'] = $stashed_real_object;
    }
    
    public function testGetContext()
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);
        
        // Act
        $actual = $activity->getContext();
        
        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }
}
