<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;
use PHPUnit\Framework\TestCase;

/**
 * ilLoggingActivityTest is part of the petri net based workflow engine.
 *
 * This class holds all tests for the class activities/class.ilLoggingActivity
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilLoggingActivityTest extends TestCase
{
    /** vfsStream Test Directory, see setup. */
    public vfs\vfsStreamDirectory $test_dir;

    private ilEmptyWorkflow $workflow;
    private ilBasicNode $node;

    protected function setUp(): void
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

    protected function tearDown(): void
    {
        global $ilSetting;
        if ($ilSetting != null) {
            //$ilSetting->delete('IL_PHPUNIT_TEST_TIME');
            //$ilSetting->delete('IL_PHPUNIT_TEST_MICROTIME');
        }
    }

    public function testConstructorValidContext(): void
    {
        // Act
        $activity = new ilLoggingActivity($this->node);

        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetValidLogFile(): void
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = './Services/WorkflowEngine/test/testlog.txt';

        // Act
        $activity->setLogFile($expected);
        $actual = $activity->getLogFile();

        $this->assertEquals(
            $actual,
            $expected,
            'Valid log file was given, returned value differed.'
        );
    }

    /**
     *
     */
    public function testSetGetNonWriteableLogFile(): void
    {
        $this->expectException(ilWorkflowFilesystemException::class);

        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = '/dev/ilias_unit_test_log_file_can_be_deleted_safely.txt';

        // Act
        $activity->setLogFile($expected);
        $actual = $activity->getLogFile();

        // Assertion via phpdoc. (Exception)
    }

    /**
     *
     */
    public function testSetGetIllegalExtensionLogFile(): void
    {
        $this->expectException(ilWorkflowObjectStateException::class);

        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = './Services/WorkflowEngine/test/malicious.php';
        // Is either one of: .log or .txt

        // Act
        $activity->setLogFile($expected);
        $actual = $activity->getLogFile();

        // Assertion via phpdoc. (Exception)
    }

    public function testSetGetLegalMessage(): void
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = 'Hallo Spencer!';

        // Act
        $activity->setLogMessage($expected);
        $actual = $activity->getLogMessage();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            'Get/Set corrupted message.'
        );
    }

    /**
     *
     */
    public function testSetGetEmptyLogMessage(): void
    {
        $this->expectException(ilWorkflowObjectStateException::class);

        // Arrange
        $activity = new ilLoggingActivity($this->node);

        // Act
        $activity->setLogMessage('');
        $actual = $activity->getLogMessage();

        // Assertion via phpdoc. (Exception)
    }

    public function testSetGetValidLogLevel(): void
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = "MESSAGE";

        // Act
        $activity->setLogLevel($expected);
        $actual = $activity->getLogLevel();

        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            'Get/Set corrupted log level.'
        );
    }

    /**
     *
     */
    public function testSetGetInvalidLogLevel(): void
    {
        $this->expectException(ilWorkflowObjectStateException::class);

        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = "guenther";

        // Act
        $activity->setLogLevel($expected);
        $actual = $activity->getLogLevel();
    }

    public function testExecute(): void
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $activity->setLogFile(vfs\vfsStream::url('example/log.txt'));
        $activity->setLogLevel('MESSAGE');
        $activity->setLogMessage('TEST');

        // Act
        $activity->execute();

        // Assert
        $expected = ' :: MESSAGE :: TEST';
        $fp = fopen(vfs\vfsStream::url('example/log.txt'), 'rb');
        $line = fgets($fp);
        $actual = substr($line, 25, -2);

        $this->assertEquals(
            $actual,
            $expected,
            'Logging Activity did not write expected output.'
        );
    }

    /**
     *
     */
    public function testPassInUnwriteablePath(): void
    {
        $this->expectException(ilWorkflowFilesystemException::class);

        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $activity->setLogFile(vfs\vfsStream::url('example.txt'));
    }

    public function testGetContext(): void
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);

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
