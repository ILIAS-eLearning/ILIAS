<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilWorkflowEngineElement.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';

/**
 * Class ilLoggingActivity
 *
 * This activity writes a given message with loglevel to the given logfile.
 * Design consideration is to configure the activity at the workflows creation
 * time, since it is triggered on known conditions.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilLoggingActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;

    /** @var string $log_file Path and filename, e.g. 'c:\wfe.log' */
    private $log_file = 'none.log';

    /** @var string $log_message Messagetext, please be descriptive. */
    private $log_message = 'no message set';

    /**
     * Log-Level of the message to be logged.
     * Valid levels are: FATAL, WARNING, MESSAGE
     *
     * @var string One of FATAL, WARNING, MESSAGE
     */
    private $log_level = 'MESSAGE';

    /** @var string $name */
    protected $name;

    /**
     * Default constructor.
     *
     * @param ilNode $a_context
     */
    public function __construct(ilNode $a_context)
    {
        $this->context = $a_context;
    }

    /**
     * Sets the log file name and path.
     *
     * @param string $a_log_file Path, name and extension of the log file.
     *
     * @return void
     */
    public function setLogFile($a_log_file)
    {
        $extension = substr($a_log_file, strlen($a_log_file) - 4, 4);
        $this->checkExtensionValidity($extension);
        $this->checkFileWriteability($a_log_file);
        $this->log_file = $a_log_file;
    }

    /**
     * Checks if the file is "really really" writeable.
     *
     * @param $a_log_file
     *
     * @return void
     *
     * @throws ilWorkflowFilesystemException
     */
    private function checkFileWriteability($a_log_file)
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @$file_handle = fopen($a_log_file, 'a+');
        if ($file_handle == null) {
            /** @noinspection PhpIncludeInspection */
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowFilesystemException.php';
            throw new ilWorkflowFilesystemException('Could not write to filesystem - no pointer returned.', 1002);
        }
        fclose($file_handle);
    }

    /**
     * Checks if the given extension is a listed one.
     * (One of .log or .txt)
     *
     * @param $extension
     *
     * @return void
     * @throws ilWorkflowObjectStateException
     */
    private function checkExtensionValidity($extension)
    {
        if ($extension != '.log' && $extension != '.txt') {
            /** @noinspection PhpIncludeInspection */
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowObjectStateException.php';
            throw new ilWorkflowObjectStateException('Illegal extension. Log file must be either .txt or .log.', 1002);
        }
    }

    /**
     * Returns the log file name and path.
     *
     * @return string File name and path of the log file.
     */
    public function getLogFile()
    {
        return $this->log_file;
    }

    /**
     * Sets the message to be logged.
     *
     * @param string $a_log_message Text of the log message
     *
     * @return void
     */
    public function setLogMessage($a_log_message)
    {
        $this->checkForExistingLogMessageContent($a_log_message);
        $this->log_message = $a_log_message;
    }

    /**
     * Checks if an actual log message is set for the instance.
     *
     * @param $a_log_message
     *
     * @return void
     *
     * @throws ilWorkflowObjectStateException
     */
    private function checkForExistingLogMessageContent($a_log_message)
    {
        if ($a_log_message == null || $a_log_message == '') {
            /** @noinspection PhpIncludeInspection */
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowObjectStateException.php';
            throw new ilWorkflowObjectStateException('Log message must not be null or empty.', 1002);
        }
    }

    /**
     * Returns the currently set log message.
     *
     * @return string
     */
    public function getLogMessage()
    {
        return $this->log_message;
    }

    /**
     * Sets the log level of the message to be logged.
     *
     * @see $log_level
     *
     * @param string $a_log_level A valid log level.
     *
     * @return void
     *
     * @throws ilWorkflowObjectStateException on illegal log level.
     */
    public function setLogLevel($a_log_level)
    {
        $valid = $this->determineValidityOfLogLevel($a_log_level);
        if ($valid == false) {
            /** @noinspection PhpIncludeInspection */
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowObjectStateException.php';
            throw new ilWorkflowObjectStateException('Log level must be one of: message, warning, debug, info, fatal.', 1002);
        }
        $this->log_level = strtoupper($a_log_level);
    }

    /**
     * Determines, if the given log level is a valid one.
     * Log levels are similar to Apache log4j levels.
     *
     * @param $a_log_level
     *
     * @return bool
     */
    private function determineValidityOfLogLevel($a_log_level)
    {
        switch (strtolower($a_log_level)) {
            case 'trace':
            case 'message':
            case 'warning':
            case 'debug':
            case 'info':
            case 'fatal':
                $valid = true;
                break;
            default:
                $valid = false;
                return $valid;
        }
        return $valid;
    }

    /**
     * Returns the currently set log level.
     *
     * @return string
     */
    public function getLogLevel()
    {
        return $this->log_level;
    }

    /**
     * Returns the parent object. Type is ilNode, implements ilWorkflowEngineElement
     *
     * @return ilNode Parent node of this element.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Executes this action according to its settings.
     *
     * @return void
     */
    public function execute()
    {
        $file_pointer = null;
        $file_pointer = $this->acquireFilePointer();
        $this->writeLogMessage($file_pointer);
        $this->closeFilePointer($file_pointer);
    }

    /**
     * Closes the file pointer.
     *
     * @param $file_pointer
     *
     * @return void
     *
     * @throws ilWorkflowFilesystemException
     */
    private function closeFilePointer($file_pointer)
    {
        if (!fclose($file_pointer)) {
            /** @noinspection PhpIncludeInspection */
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowFilesystemException.php';
            throw new ilWorkflowFilesystemException('Cannot write to filesystem - pointer returned did not close.', 1001);
        }
    }

    /**
     * Writes the instances log message to the logfile.
     *
     * @param $file_pointer
     *
     * @return void
     */
    private function writeLogMessage($file_pointer)
    {
        /** @noinspection PhpIncludeInspection */
        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
        fwrite($file_pointer, date('Y/m/d H:i:s') . substr((string) ilWorkflowUtils::microtime(), 1, 6) . ' :: ');
        fwrite($file_pointer, $this->log_level . ' :: ');
        fwrite($file_pointer, $this->log_message . "\r\n");
    }

    /**
     * Acquires and returns a file pointer to the instances log file.
     *
     * @return resource File pointer
     *
     * @throws ilWorkflowFilesystemException
     */
    private function acquireFilePointer()
    {
        $file_pointer = fopen($this->log_file, 'a');
        if ($file_pointer == null) {
            /** @noinspection PhpIncludeInspection */
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowFilesystemException.php';
            throw new ilWorkflowFilesystemException('Cannot write to filesystem - no pointer returned.', 1000);
        }
        return $file_pointer;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
