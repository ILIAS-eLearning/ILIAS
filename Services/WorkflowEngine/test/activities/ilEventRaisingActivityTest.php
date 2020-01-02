<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;

/**
 * ilEventRaisingActivityTest is part of the workflow engine.
 *
 * This class holds all tests for the class activities/class.ilEventRaisingActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilEventRaisingActivityTest extends PHPUnit_Framework_TestCase
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
                
        require_once './Services/WorkflowEngine/classes/activities/class.ilEventRaisingActivity.php';

        $this->test_dir = vfs\vfsStream::setup('example');
    }

    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting !=  null) {
            //$ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            //$ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext()
    {
        // Act
        $activity = new ilEventRaisingActivity($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetEventNameShouldReturnSetValue()
    {
        // Arrange
        $activity = new ilEventRaisingActivity($this->node);
        $expected = 'HokusPokus';

        // Act
        $activity->setEventName($expected);
        $actual = $activity->getEventName();

        $this->assertEquals(
            $actual,
            $expected,
            'Retrieved EventName differs from input value.'
        );
    }

    public function testSetGetEventTypeShouldReturnSetValue()
    {
        // Arrange
        $activity = new ilEventRaisingActivity($this->node);
        $expected = 'HokusPokus';

        // Act
        $activity->setEventType($expected);
        $actual = $activity->getEventType();

        $this->assertEquals(
            $actual,
            $expected,
            'Retrieved EventType differs from input value.'
        );
    }

    public function testGetContext()
    {
        // Arrange
        $activity = new ilEventRaisingActivity($this->node);

        // Act
        $actual = $activity->getContext();

        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }

    public function testSetGetFixedParamsSinglePair()
    {
        // Arrange
        $activity = new ilEventRaisingActivity($this->node);
        $key = 'Key';
        $value = 123;
        $expected[] = array('key' => $key, 'value' => $value);
        $expected[] = array('key' => 'context', 'value' => $activity);

        // Act
        $activity->addFixedParam($key, $value);
        $params = $activity->getParamsArray();

        // Assert
        $this->assertEquals($expected, $params);
    }

    public function testSetGetFixedParamsMultiplePairs()
    {
        // Arrange
        $activity = new ilEventRaisingActivity($this->node);
        $key1 = 'Key1';
        $value1 = 123;
        $key2 = 'Key2';
        $value2 = 234;
        $key3 = 'Key3';
        $value3 = 345;
        $expected[] = array('key' => $key1, 'value' => $value1);
        $expected[] = array('key' => $key2, 'value' => $value2);
        $expected[] = array('key' => $key3, 'value' => $value3);
        $expected[] = array('key' => 'context', 'value' => $activity);

        // Act
        $activity->addFixedParam($key1, $value1);
        $activity->addFixedParam($key2, $value2);
        $activity->addFixedParam($key3, $value3);
        $params = $activity->getParamsArray();

        // Assert
        $this->assertEquals($expected, $params);
    }
}
