<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilLoggingActivity
 *
 * This activity writes a given message with loglevel to the given logfile.
 * Design consideration is to configure the activity at the workflows creation
 * time, since it is triggered on known conditions.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilLoggingActivity implements ilActivity, ilWorkflowEngineElement
{
    /** @var ilWorkflowEngineElement $context Holds a reference to the parent object */
    private $context;

    /** Path and filename, e.g. 'c:\wfe.log' */
    private string $log_file = 'none.log';

    /** Messagetext, please be descriptive. */
    private string $log_message = 'no message set';

    /**
     * Log-Level of the message to be logged.
     * Valid levels are: FATAL, WARNING, MESSAGE
     *
     * One of FATAL, WARNING, MESSAGE
     */
    private string $log_level = 'MESSAGE';

    protected string $name = '';

    public function __construct(ilNode $a_context)
    {
        $this->context = $a_context;
    }

    /**
     * Sets the log file name and path.
     * @param string $a_log_file Path, name and extension of the log file.
     * @return void
     */
    public function setLogFile(string $a_log_file) : void
    {
        $extension = substr($a_log_file, strlen($a_log_file) - 4, 4);
        $this->checkExtensionValidity($extension);
        $this->checkFileWriteability($a_log_file);
        $this->log_file = $a_log_file;
    }

    /**
     * Checks if the file is "really really" writeable.
     * @throws ilWorkflowFilesystemException
     */
    private function checkFileWriteability(string $a_log_file) : void
    {
        if (!is_writable(dirname($a_log_file))) {
            throw new ilWorkflowFilesystemException('Could not write to filesystem - no pointer returned.', 1002);
        }

        $file_handle = fopen($a_log_file, 'ab+');
        if (!is_resource($file_handle)) {
            throw new ilWorkflowFilesystemException('Could not write to filesystem - no pointer returned.', 1002);
        }
        fclose($file_handle);
    }

    /**
     * Checks if the given extension is a listed one.
     * (One of .log or .txt)
     * @throws ilWorkflowObjectStateException
     */
    private function checkExtensionValidity(string $extension) : void
    {
        if ($extension !== '.log' && $extension !== '.txt') {
            throw new ilWorkflowObjectStateException('Illegal extension. Log file must be either .txt or .log.', 1002);
        }
    }

    /**
     * Returns the log file name and path.
     *
     * @return string File name and path of the log file.
     */
    public function getLogFile() : string
    {
        return $this->log_file;
    }

    /**
     * Sets the message to be logged.
     * @param string $a_log_message Text of the log message
     * @return void
     */
    public function setLogMessage(string $a_log_message) : void
    {
        $this->checkForExistingLogMessageContent($a_log_message);
        $this->log_message = $a_log_message;
    }

    /**
     * Checks if an actual log message is set for the instance.
     * @throws ilWorkflowObjectStateException
     */
    private function checkForExistingLogMessageContent(?string $a_log_message) : void
    {
        if ($a_log_message === null || $a_log_message === '') {
            throw new ilWorkflowObjectStateException('Log message must not be null or empty.', 1002);
        }
    }

    /**
     * Returns the currently set log message.
     *
     * @return string
     */
    public function getLogMessage() : string
    {
        return $this->log_message;
    }

    /**
     * Sets the log level of the message to be logged.
     * @param string $a_log_level A valid log level.
     * @return void
     * @throws ilWorkflowObjectStateException on illegal log level.
     * @see $log_level
     */
    public function setLogLevel(string $a_log_level) : void
    {
        $valid = $this->determineValidityOfLogLevel($a_log_level);
        if ($valid === false) {
            throw new ilWorkflowObjectStateException('Log level must be one of: message, warning, debug, info, fatal.', 1002);
        }
        $this->log_level = strtoupper($a_log_level);
    }

    /**
     * Determines, if the given log level is a valid one.
     * Log levels are similar to Apache log4j levels.
     */
    private function determineValidityOfLogLevel(string $a_log_level) : bool
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
    public function getLogLevel() : string
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
     * @return void
     */
    public function execute() : void
    {
        $file_pointer = $this->acquireFilePointer();
        $this->writeLogMessage($file_pointer);
        $this->closeFilePointer($file_pointer);
    }

    /**
     * Closes the file pointer.
     *
     * @param resource$file_pointer
     * @return void
     * @throws ilWorkflowFilesystemException
     */
    private function closeFilePointer($file_pointer) : void
    {
        if (!fclose($file_pointer)) {
            throw new ilWorkflowFilesystemException('Cannot write to filesystem - pointer returned did not close.', 1001);
        }
    }

    /**
     * Writes the instances log message to the logfile.
     *
     * @param resource $file_pointer
     * @return void
     */
    private function writeLogMessage($file_pointer) : void
    {
        fwrite($file_pointer, date('Y/m/d H:i:s') . substr(ilWorkflowUtils::microtime(), 1, 6) . ' :: ');
        fwrite($file_pointer, $this->log_level . ' :: ');
        fwrite($file_pointer, $this->log_message . "\r\n");
    }

    /**
     * Acquires and returns a file pointer to the instances log file.
     *
     * @return resource File pointer
     * @throws ilWorkflowFilesystemException
     */
    private function acquireFilePointer()
    {
        $file_pointer = fopen($this->log_file, 'ab');
        if (!is_resource($file_pointer)) {
            throw new ilWorkflowFilesystemException('Cannot write to filesystem - no pointer returned.', 1000);
        }

        return $file_pointer;
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
