<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use org\bovigo\vfs;

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
class ilLoggingActivityTest extends PHPUnit_Framework_TestCase
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
                
        require_once './Services/WorkflowEngine/classes/activities/class.ilLoggingActivity.php';

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
        $activity = new ilLoggingActivity($this->node);
        
        // Assert
        // No exception - good
        $this->assertTrue(
            true,
            'Construction failed with valid context passed to constructor.'
        );
    }

    public function testSetGetValidLogFile()
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
     * @expectedException ilWorkflowFilesystemException
     */
    public function testSetGetNonWriteableLogFile()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = '/dev/ilias_unit_test_log_file_can_be_deleted_safely.txt';
                
        // Act
        $activity->setLogFile($expected);
        $actual = $activity->getLogFile();
        
        // Assertion via phpdoc. (Exception)
    }

    /**
     * @expectedException ilWorkflowObjectStateException
     */
    public function testSetGetIllegalExtensionLogFile()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = './Services/WorkflowEngine/test/malicious.php';
        // Is either one of: .log or .txt
        
        // Act
        $activity->setLogFile($expected);
        $actual = $activity->getLogFile();
        
        // Assertion via phpdoc. (Exception)
    }
    
    public function testSetGetLegalMessage()
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
     * @expectedException ilWorkflowObjectStateException
     */
    public function testSetGetNullLogMessage()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);

        // Act
        $activity->setLogMessage(null);
        $actual = $activity->getLogMessage();
        
        // Assertion via phpdoc. (Exception)
    }

    /**
     * @expectedException ilWorkflowObjectStateException
     */
    public function testSetGetEmptyLogMessage()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);

        // Act
        $activity->setLogMessage('');
        $actual = $activity->getLogMessage();
        
        // Assertion via phpdoc. (Exception)
    }

    public function testSetGetValidLogLevel()
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
     * @expectedException ilWorkflowObjectStateException
     */
    public function testSetGetInvalidLogLevel()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $expected = "guenther";
        
        // Act
        $activity->setLogLevel($expected);
        $actual = $activity->getLogLevel();
    }

    public function testExecute()
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
        $fp = fopen(vfs\vfsStream::url('example/log.txt'), 'r');
        $line = fgets($fp);
        $actual = substr($line, 25, strlen($line)-27);

        $this->assertEquals(
            $actual,
            $expected,
            'Logging Activity did not write expected output.'
        );
    }

    /**
     * @expectedException ilWorkflowFilesystemException
     */
    public function testPassInUnwriteablePath()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        $activity->setLogFile(vfs\vfsStream::url('example.txt'));
    }

    public function testGetContext()
    {
        // Arrange
        $activity = new ilLoggingActivity($this->node);
        
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
