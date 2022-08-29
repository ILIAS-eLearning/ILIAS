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
class ilPluginNodeTest extends ilWorkflowEngineBaseTest
{
    /** @var ilBaseWorkflow $workflow */
    public $workflow;

    protected function setUp(): void
    {
        // Empty workflow.
        require_once './Services/WorkflowEngine/classes/workflows/class.ilEmptyWorkflow.php';
        $this->workflow = new ilEmptyWorkflow();
    }

    protected function tearDown(): void
    {
        global $DIC;

        if (isset($DIC['ilSetting'])) {
            $DIC['ilSetting']->delete('IL_PHPUNIT_TEST_TIME');
            $DIC['ilSetting']->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }

    public function testConstructorValidContext(): void
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

    public function testGetContext(): void
    {
        // Arrange
        $node = new ilPluginNode($this->workflow);

        // Act
        $actual = $node->getContext();

        // Assert
        if ($actual === $this->workflow) {
            $this->assertEquals($actual, $this->workflow);
        } else {
            $this->fail('Context not identical.');
        }
    }

    public function testIsActiveAndActivate(): void
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

    public function testDeactivate(): void
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
