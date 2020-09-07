<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilPluginNodeTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class
 * nodes/class.ilPluginNode.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilPluginNodeTest extends PHPUnit_Framework_TestCase
{
    /** @var ilBaseWorkflow $workflow */
    public $workflow;

    public function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        //ilUnitUtil::performInitialisation();
        
        // Empty workflow.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
        $this->workflow = new ilEmptyWorkflow();
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
        $node = new ilPluginNode($this->workflow);
        
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
        $node = new ilPluginNode($this->workflow);

        // Act
        $actual = $node->getContext();

        // Assert
        if ($actual === $this->workflow) {
            $this->assertEquals($actual, $this->workflow);
        } else {
            $this->assertTrue(false, 'Context not identical.');
        }
    }

    public function testIsActiveAndActivate()
    {
        // Arrange
        $node = new ilPluginNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        // Act
        $node->activate();

        // Assert
        $actual = $node->isActive();
        $this->assertTrue($actual);
    }

    public function testDeactivate()
    {
        // Arrange
        $node = new ilPluginNode($this->workflow);
        require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
        $detector = new ilSimpleDetector($node);
        $node->addDetector($detector);

        // Act
        $node->activate();

        // Assert
        $this->assertTrue($node->isActive(), 'Node should be active but is inactive.');
        $node->deactivate();
        $this->assertFalse($node->isActive(), 'Node should be inactive but is active.');
    }
}
