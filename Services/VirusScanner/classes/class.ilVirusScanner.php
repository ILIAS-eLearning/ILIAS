<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for the interface to an external virus scanner
 * This class is abstract and needs to be extended for actual scanners
 * Only scanFile() and cleanFile() need to be redefined
 * Child Constructors should call ilVirusScanner()
 * Scan and Clean are independent and may work on different files
 * Logging and message generation are generic
 * @author    Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @version   $Id$
 */
class ilVirusScanner
{
    public string $type;

    public bool $scanZipFiles;

    public string $scanCommand;

    public string $cleanCommand;

    public string $scanFilePath;

    public string $scanFileOrigName;

    public string $cleanFilePath;

    public string $cleanFileOrigName;

    public bool $scanFileIsInfected;

    public bool $cleanFileIsCleaned;

    public string $scanResult;

    public string $cleanResult;

    /**
     * Ilias object
     * @var object
     * @access private
     */
    public $ilias;

    public ilLanguage $lng;

    public ilLog $log;

    public function __construct(string $scan_command, string $clean_command)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];
        $log = $DIC['log'];

        $this->ilias = $ilias;
        $this->lng = $lng;
        $this->log = $log;
        $this->scanCommand = $scan_command;
        $this->cleanCommand = $clean_command;

        $this->type = "simulate";
        $this->scanZipFiles = false;
    }

    public function scanBuffer(string $buffer) : bool
    {
        return $this->scanFileFromBuffer($buffer);
    }

    protected function scanFileFromBuffer(string $buffer) : bool
    {
        $bufferFile = $this->createBufferFile($buffer);
        $isInfected = $this->scanFile($bufferFile);
        $this->removeBufferFile($bufferFile);
        return $isInfected;
    }

    protected function createBufferFile(string $buffer) : string
    {
        $bufferFile = ilFileUtils::ilTempnam();
        file_put_contents($bufferFile, $buffer);
        return $bufferFile;
    }

    public function scanFile(string $file_path, string $org_name = "") : string
    {
        $this->scanFilePath = $file_path;
        $this->scanFileOrigName = $org_name;

        if ($org_name == "infected.txt" or $org_name == "cleanable.txt") {
            $this->scanFileIsInfected = true;
            $this->scanResult =
                "FILE INFECTED: [" . $file_path . "] (VIRUS: simulated)";
            $this->logScanResult();
            return $this->scanResult;
        } else {
            $this->scanFileIsInfected = false;
            $this->scanResult = "";
            return "";
        }
    }

    public function logScanResult() : void
    {
        $mess = "Virus Scanner (" . $this->type . ")";
        if ($this->scanFileOrigName) {
            $mess .= " (File " . $this->scanFileOrigName . ")";
        }
        $mess .= ": " . preg_replace('/[\r\n]+/', "; ", $this->scanResult);

        $this->log->write($mess);
    }

    protected function removeBufferFile(string $bufferFile) : void
    {
        unlink($bufferFile);
    }

    public function cleanFile(string $file_path, string $org_name = "") : string
    {
        $this->cleanFilePath = $file_path;
        $this->cleanFileOrigName = $org_name;

        if ($org_name == "cleanable.txt") {
            $this->cleanFileIsCleaned = true;
            $this->cleanResult =
                "FILE CLEANED: [" . $file_path . "] (VIRUS: simulated)";
            $this->logCleanResult();
            return $this->cleanResult;
        } else {
            $this->cleanFileIsCleaned = false;
            $this->cleanResult =
                "FILE NOT CLEANED: [" . $file_path . "] (VIRUS: simulated)";
            $this->logCleanResult();
            return "";
        }
    }

    public function logCleanResult() : void
    {
        $mess = "Virus Cleaner (" . $this->type . ")";
        if ($this->cleanFileOrigName) {
            $mess .= " (File " . $this->cleanFileOrigName . ")";
        }
        $mess .= ": " . preg_replace('/[\r\n]+/', "; ", $this->cleanResult);

        $this->log->write($mess);
    }

    public function fileCleaned() : bool
    {
        return $this->cleanFileIsCleaned;
    }

    public function getScanResult() : string
    {
        return $this->scanResult;
    }

    public function getCleanResult() : string
    {
        return $this->cleanResult;
    }

    public function getScanMessage() : string
    {
        if ($this->scanFileIsInfected) {
            $ret = sprintf($this->lng->txt("virus_infected"), $this->scanFileOrigName);
        } else {
            $ret = sprintf($this->lng->txt("virus_not_infected"), $this->scanFileOrigName);
        }

        if ($this->scanResult) {
            $ret .= " " . $this->lng->txt("virus_scan_message")
                . "<br />"
                . str_replace(
                    $this->scanFilePath,
                    $this->scanFileOrigName,
                    nl2br($this->scanResult)
                );
        }
        return $ret;
    }

    public function getCleanMessage() : string
    {
        if ($this->cleanFileIsCleaned) {
            $ret = sprintf($this->lng->txt("virus_cleaned"), $this->cleanFileOrigName);
        } else {
            $ret = sprintf($this->lng->txt("virus_not_cleaned"), $this->cleanFileOrigName);
        }

        if ($this->cleanResult) {
            $ret .= " " . $this->lng->txt("virus_clean_message")
                . "<br />"
                . str_replace(
                    $this->cleanFilePath,
                    $this->cleanFileOrigName,
                    nl2br($this->cleanResult)
                );
        }
        return $ret;
    }

    public function getScanZipFiles() : bool
    {
        return $this->scanZipFiles;
    }
}
