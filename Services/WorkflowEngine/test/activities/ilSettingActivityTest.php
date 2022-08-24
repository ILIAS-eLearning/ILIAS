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
    private ilEmptyWorkflow $workflow;
    private ilBasicNode $node;

    protected function setUp(): void
    {
        parent::setUp();

        // Empty workflow.
        $this->workflow = new ilEmptyWorkflow();

        // Basic node
        $this->node = new ilBasicNode($this->workflow);

        // Wiring up so the node is attached to the workflow.
        $this->workflow->addNode($this->node);
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
        $activity = new ilSettingActivity($this->node);

        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetSettingName(): void
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

    public function testSetGetSettingValue(): void
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

    public function testSetSetting(): void
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);

        // Act
        $expected_name = 'Ralle';
        $expected_value = 'OK';
        $activity->setSetting($expected_name, $expected_value);
        $actual_name = $activity->getSettingName();
        $actual_value = $activity->getSettingValue();

        // Assert
        $this->assertEquals(
            $actual_name . $actual_value,
            $expected_name . $expected_value
        );
    }

    public function testExecute(): void
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);
        $expected_name = 'Ralle';
        $expected_val = 'OK';
        $activity->setSetting($expected_name, $expected_val);

        $ilSetting_mock = $this->createMock(ilSetting::class);

        $ilSetting_mock->expects($this->once())
                       ->method('set')
                       ->with($expected_name, $expected_val);

        $stashed_real_object = $GLOBALS['DIC']['ilSetting'] ?? '';

        unset($GLOBALS['DIC']['ilSetting']);
        $GLOBALS['DIC']['ilSetting'] = $ilSetting_mock;

        // Act
        $activity->execute();

        $GLOBALS['DIC']['ilSetting'] = $stashed_real_object;
    }

    public function testGetContext(): void
    {
        // Arrange
        $activity = new ilSettingActivity($this->node);

        // Act
        $actual = $activity->getContext();

        // Assert
        if ($actual === $this->node) {
            $this->assertEquals($actual, $this->node);
        } else {
            $this->fail('Context not identical.');
        }
    }
}
