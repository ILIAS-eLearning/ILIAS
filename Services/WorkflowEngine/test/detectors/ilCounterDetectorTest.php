<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilCounterDetectorTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * detectors/class.ilCounterDetector
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilCounterDetectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
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
        
        require_once './Services/WorkflowEngine/classes/detectors/class.ilCounterDetector.php';
    }
    
    public function tearDown()
    {
        global $ilSetting;
        if ($ilSetting !=  null) {
            $ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            $ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext()
    {
        // Act
        $detector = new ilCounterDetector($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetExpectedTriggerEvents()
    {
        // Arrange
        $detector = new ilCounterDetector($this->node);
        $expected = 4711;
        
        // Act
        $detector->setExpectedTriggerEvents($expected);
        $actual = $detector->getExpectedTriggerEvents();
        
        // Assert
        $this->assertEquals($actual, $expected);
    }
    
    public function testSetGetActualTriggerEvents()
    {
        // Arrange
        $detector = new ilCounterDetector($this->node);
        $expected = 4711;
        
        // Act
        $detector->setActualTriggerEvents($expected);
        $actual = $detector->getActualTriggerEvents();
        
        // Assert
        $this->assertEquals($actual, $expected);
    }
    
    public function testTriggerUnsatisfy()
    {
        // Arrange
        $detector = new ilCounterDetector($this->node);
        $expected = 2;
        $detector->setExpectedTriggerEvents($expected);

        // Act
        $detector->trigger(null);
        
        // Assert
        $valid_state = true;
        if ($detector->getActualTriggerEvents() != 1) {
            $valid_state = false;
        }
        
        if ($detector->getExpectedTriggerEvents() != $expected) {
            $valid_state = false;
        }

        if ($detector->getDetectorState()) {
            $valid_state = false;
        }
        
        $this->assertTrue($valid_state, 'Detector state invalid.');
    }
    
    public function testTriggerSatisfy()
    {
        // Arrange
        $detector = new ilCounterDetector($this->node);
        $expected = 2;
        $detector->setExpectedTriggerEvents($expected);

        // Act
        $detector->trigger(null);
        $detector->trigger(null);
        
        // Assert
        $valid_state = true;
        
        if ($detector->getActualTriggerEvents() != 2) {
            $valid_state = false;
        }
        
        if ($detector->getExpectedTriggerEvents() != $expected) {
            $valid_state = false;
        }

        if (!$detector->getDetectorState()) {
            $valid_state = false;
        }
        
        $this->assertTrue($valid_state, 'Detector state invalid.');
    }

    public function testTriggerOversatisfy()
    {
        // Arrange
        $detector = new ilCounterDetector($this->node);
        $expected = 2;
        $detector->setExpectedTriggerEvents($expected);

        // Act & Assert
        $this->assertTrue($detector->trigger(null));
        $this->assertTrue($detector->trigger(null));
        $this->assertFalse($detector->trigger(null));
    }

    public function testGetContext()
    {
        // Arrange
        $detector = new ilCounterDetector($this->node);
        
        // Act
        $actual = $detector->getContext();
        
        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }
}
