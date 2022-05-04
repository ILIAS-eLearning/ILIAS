<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;
use PHPUnit\Framework\TestCase;

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
class ilScriptActivityTest extends TestCase
{
    private ilEmptyWorkflow $workflow;
    private ilBasicNode $node;
    /** vfsStream Test Directory, see setup. */
    public vfs\vfsStreamDirectory $test_dir;

    protected function setUp() : void
    {
        chdir(__DIR__);
        chdir('../../../../');

        // Empty workflow.
        $this->workflow = new ilEmptyWorkflow();
        
        // Basic node
        $this->node = new ilBasicNode($this->workflow);
        
        // Wiring up so the node is attached to the workflow.
        $this->workflow->addNode($this->node);
                
        $this->test_dir = vfs\vfsStream::setup('example');
    }

    protected function tearDown() : void
    {
        global $ilSetting;
        if ($ilSetting != null) {
            //$ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            //$ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }
    
    public function testConstructorValidContext() : void
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

    public function testGetContext() : void
    {
        // Arrange
        $activity = new ilScriptActivity($this->node);

        // Act
        $actual = $activity->getContext();

        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->fail('Context not identical.');
        }
    }

    public function testSetGetMethod() : void
    {
        // Arrange
        $activity = new ilScriptActivity($this->node);

        $activity->setMethod(function () {
            return 'Hallo, Welt!';
        });

        // Act
        $response = $activity->getScript();

        // Assert
        $this->assertEquals("Hallo, Welt!", $response());
    }
}
