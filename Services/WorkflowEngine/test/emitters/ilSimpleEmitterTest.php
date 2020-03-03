<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilSimpleEmitterTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * emitters/class.ilSimpleEmitter
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSimpleEmitterTest extends PHPUnit_Framework_TestCase
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
        
        require_once './Services/WorkflowEngine/classes/emitters/class.ilSimpleEmitter.php';
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
        $emitter = new ilSimpleEmitter($this->node);
        
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
        $emitter = new ilSimpleEmitter($this->node);
        
        // Act
        $actual = $emitter->getContext();
        
        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }
    
    public function testSetGetTargetDetector()
    {
        // Arrange
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $target_node = new ilBasicNode($this->workflow);
        $target_detector = new ilSimpleDetector($target_node);
        $target_node->addDetector($target_detector);
        
        $emitter = new ilSimpleEmitter($this->node);
        
        // Act
        $emitter->setTargetDetector($target_detector);
        
        // Assert
        $actual = $emitter->getTargetDetector();
        $this->assertEquals($target_detector, $actual);
    }
    
    public function testEmitValidState()
    {
        // Arrange
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $target_node = new ilBasicNode($this->workflow);
        $target_detector = new ilSimpleDetector($target_node);
        $target_node->addDetector($target_detector);
        
        $emitter = new ilSimpleEmitter($this->node);
        $emitter->setTargetDetector($target_detector);
        
        // Act
        $emitter->emit();
        // Assert
        $actual = $target_detector->getDetectorState();
        $this->assertTrue($actual);
    }
}
