<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface to the ClamAV virus protector
 * @author        Ralf Schenk <rs@databay.de>
 * @version       $Id$
 * @extends       ilVirusScanner
 */

require_once "./Services/VirusScanner/classes/class.ilVirusScanner.php";

class ilVirusScannerClamAV extends ilVirusScanner
{
    const ADD_SCAN_PARAMS = '--no-summary -i';

    public function __construct(string $scan_command, string $clean_command)
    {
        parent::__construct($scan_command, $clean_command);
        $this->type = "clamav";
        $this->scanZipFiles = true;
    }

    public function scanBuffer(string $buffer) : bool
    {
        if (!$this->isBufferScanPossible()) {
            return $this->scanFileFromBuffer($buffer);
        }

        return $this->processBufferScan($buffer);
    }

    protected function isBufferScanPossible() : bool
    {
        $functions = array('proc_open', 'proc_close');

        foreach ($functions as $func) {
            if (function_exists($func)) {
                continue;
            }

            return false;
        }

        return true;
    }

    protected function processBufferScan(string $buffer) : bool
    {
        $descriptor_spec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w")        // stderr for the child
        );

        $pipes = array(); // will look like follows after passing
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout

        $process = proc_open($this->buildScanCommand(), $descriptor_spec, $pipes);

        if (!is_resource($process)) {
            return false; // no scan, no virus detected
        }

        fwrite($pipes[0], $buffer);
        fclose($pipes[0]);

        $detectionReport = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $errorReport = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $return = proc_close($process);

        return $this->hasDetections($detectionReport);
    }

    protected function buildScanCommand(string $file = '-') : string
    {
        return $this->scanCommand . ' ' . self::ADD_SCAN_PARAMS . ' ' . $file;
    }

    protected function hasDetections(string $detectionReport) : int
    {
        return preg_match("/FOUND/", $detectionReport);
    }

    public function scanFile(string $file_path, string $org_name = "") : string
    {
        $this->scanFilePath = $file_path;
        $this->scanFileOrigName = $org_name;
        // Make group readable for clamdscan
        $perm = fileperms($file_path) | 0640;
        chmod($file_path, $perm);

        // Call of antivir command
        $cmd = $this->buildScanCommand($file_path) . " 2>&1";
        exec($cmd, $out, $ret);
        $this->scanResult = implode("\n", $out);

        // sophie could be called
        if ($this->hasDetections($this->scanResult)) {
            $this->scanFileIsInfected = true;
            $this->logScanResult();
            return $this->scanResult;
        } else {
            $this->scanFileIsInfected = false;
            return "";
        }
    }
}
