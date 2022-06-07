<?php

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
 * @deprecated
 * this class provides a logging feature to the application
 * this class is easy to use.
 * call the constructor with e.g.
 * $log = new Log();
 * you can give a filename if you want, else the defaultfilename is used.
 *
 * @author	Peter Gabriel <pgabriel@databay.de>
 * @version	$Id: class.ilLog.php 16024 2008-02-19 13:07:07Z akill $
 */
class ilLog
{
    private string $path = '';
    private string $filename = '';
    private string $tag = '';
    private string $log_format = '';

    /**
    * Log level 10: Log only fatal errors that could lead to serious problems
    */
    private int $FATAL;

    /**
    * Log level 20: This is the standard log level that is set if no level is given
    */
    private int $WARNING;

    /**
    * Log level 30: Logs messages and notices that are less important for system functionality like not translated language values
    */
    private int $MESSAGE;

    /**
     * @var null|resource
     */
    private $fp = null;

    protected int $default_log_level;
    protected int $current_log_level;
    protected bool $enabled;

    public function __construct(
        string $a_log_path,
        string $a_log_file,
        string $a_tag = "",
        bool $a_enabled = true,
        ?int $a_log_level = null
    ) {
        // init vars

        $this->FATAL = ilLogLevel::CRITICAL;
        $this->WARNING = ilLogLevel::WARNING;
        $this->MESSAGE = ilLogLevel::INFO;

        $this->default_log_level = $this->WARNING;
        $this->current_log_level = $this->setLogLevel($a_log_level ?? $this->default_log_level);

        $this->path = ($a_log_path) ?: ILIAS_ABSOLUTE_PATH;
        $this->filename = ($a_log_file) ?: "ilias.log";
        $this->tag = ($a_tag == "") ? "unknown" : $a_tag;
        $this->enabled = $a_enabled;
        $this->setLogFormat(date("[y-m-d H:i:s] ") . "[" . $this->tag . "] ");
        $this->open();
    }

    public function setLogLevel(int $a_log_level) : int
    {
        switch (strtolower($a_log_level)) {
            case "fatal":
                return $this->FATAL;
            case "warning":
                return $this->WARNING;
            case "message":
                return $this->MESSAGE;
            default:
                return $this->default_log_level;
        }
    }

    /**
     * @param int $a_log_level
     */
    public function checkLogLevel($a_log_level) : int
    {
        if (empty($a_log_level)) {
            return $this->default_log_level;
        }
        $level = (int) $a_log_level;
        if ($a_log_level != (int) $a_log_level) {
            return $this->default_log_level;
        }
        return $level;
    }

    public function setLogFormat(string $a_format) : void
    {
        $this->log_format = $a_format;
    }

    public function getLogFormat() : string
    {
        return $this->log_format;
    }

    public function setPath(string $a_str) : void
    {
        $this->path = $a_str;

        // on filename change reload close current file
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    public function setFilename(string $a_str) : void
    {
        $this->filename = $a_str;

        // on filename change reload close current file
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    public function setTag(string $a_str) : void
    {
        $this->tag = $a_str;
    }

    /**
    * special language checking routine
    *
    * only add a log entry to the logfile
    * if there isn't a log entry for the topic
    */
    public function writeLanguageLog(string $a_topic, string $a_lang_key) : void
    {
        //TODO: go through logfile and search for the topic
        //only write the log if the error wasn't reported yet
        $this->write("Language (" . $a_lang_key . "): topic -" . $a_topic . "- not present", $this->MESSAGE);
    }

    /**
    * special warning message
    */
    public function writeWarning(string $a_message) : void
    {
        $this->write("WARNING: " . $a_message);
    }

    /**
    * this function is automatically called by class.ilErrorHandler in case of an error
    * To log manually please use $this::write
    */
    public function logError(string $a_code, string $a_msg) : void
    {
        switch ($a_code) {
            case "3":
                return; // don't log messages

            case "2":
                $error_level = "warning";
                break;

            case "1":
                $error_level = "fatal";
                break;

            default:
                $error_level = "unknown";
                break;
        }
        $this->write("ERROR (" . $error_level . "): " . $a_msg);
    }

    /**
    * logging
    *
    * this method logs anything you want. It appends a line to the given logfile.
    * Datetime and client id is appended automatically
    * You may set the log level in each call. Leave blank to use default log level
    * specified in ilias.ini:
    * [log]
    * level = "<level>" possible values are fatal,warning,message
    *
    *
    * @param ?int $a_log_level
    *
    */
    public function write(string $a_msg, $a_log_level = null) : void
    {
        $a_log_level = (int) $a_log_level;
        if ($this->enabled and $this->current_log_level >= $this->checkLogLevel($a_log_level)) {
            $this->open();

            if ($this->fp == false) {
                //die("Logfile: cannot open file. Please give Logfile Writepermissions.");
            }

            if (fwrite($this->fp, $this->getLogFormat() . $a_msg . "\n") == -1) {
                //die("Logfile: cannot write to file. Please give Logfile Writepermissions.");
            }

            // note: logStack() calls write() again, so do not make this call
            // if no log level is given
            if ($a_log_level == $this->FATAL) {
                $this->logStack();
            }
        }
    }

    public function logStack(string $a_message = '') : void
    {
        try {
            throw new Exception($a_message);
        } catch (Exception $e) {
            $this->write($e->getTraceAsString());
        }
    }

    public function dump($a_var, ?int $a_log_level = null) : void
    {
        $this->write(print_r($a_var, true), $a_log_level);
    }

    /**
     * Open log file
     */
    private function open() : void
    {
        if (!$this->fp) {
            $this->fp = @fopen($this->path . "/" . $this->filename, "a");
        }

        if (!$this->fp && $this->enabled) {
            throw new ilLogException('Unable to open log file for writing. Please check setup path to log file and possible write access.');
        }
    }

    /**
    * delete logfile
    */
    public function delete() : void
    {
        if (is_file($this->path . "/" . $this->filename)) {
            unlink($this->path . "/" . $this->filename);
        }
    }
} // END class.ilLog
