<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilDataDetectorTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * detectors/class.ilDataDetector
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilDataDetectorTest extends PHPUnit_Framework_TestCase
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
        
        require_once './Services/WorkflowEngine/classes/detectors/class.ilDataDetector.php';
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
        $detector = new ilDataDetector($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetDetectorState()
    {
        // Arrange
        $workflow = new ilEmptyWorkflow();
        $node = new ilBasicNode($workflow);
        $detector = new ilDataDetector($node);
        $workflow->addNode($node);

        
        // Act
        $detector->setDetectorState(true);
        
        
        // Assert
        $valid_state = true;
        
        if (!$detector->getDetectorState()) {
            $valid_state = false;
        }
        
        if ($node->isActive()) {
            // With this single detector satisfied, the
            // parent node should have transitted and
            // this would result in it being inactive
            // afterwards.
            $valid_state = false;
        }
        
        $this->assertTrue($valid_state, 'Invalid state after setting of detector state.');
    }
    
    public function testTrigger()
    {
        // Arrange
        $workflow = new ilEmptyWorkflow();
        $node = new ilBasicNode($workflow);
        $detector = new ilDataDetector($node);
        $workflow->addNode($node);

        
        // Act
        $detector->trigger(null);
        
        // Assert
        $valid_state = true;
        
        if (!$detector->getDetectorState()) {
            $valid_state = false;
        }
        
        if ($node->isActive()) {
            // With this single detector satisfied, the
            // parent node should have transitted and
            // this would result in it being inactive
            // afterwards.
            $valid_state = false;
        }
        
        $this->assertTrue($valid_state, 'Invalid state after setting of detector state.');
    }
    
    public function testGetContext()
    {
        // Arrange
        $detector = new ilDataDetector($this->node);
        
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
