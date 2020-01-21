<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface to the sophos virus protector
 * @author    Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @version   $Id$
 * @extends   ilVirusScanner
 */

require_once "./Services/VirusScanner/classes/class.ilVirusScanner.php";

class ilVirusScannerSophos extends ilVirusScanner
{
    /**
     * Constructor
     * @access    public
     * @param    string virus scanner command
     */
    public function __construct($a_scancommand, $a_cleancommand)
    {
        parent::__construct($a_scancommand, $a_cleancommand);
        $this->type         = "sophos";
        $this->scanZipFiles = true;
    }

    /**
     * scan a file for viruses
     * @param    string    path of file to check
     * @param    string    original name of the file to ckeck
     * @return   string  virus message (empty if not infected)
     * @access    public
     */
    public function scanFile($a_filepath, $a_origname = "")
    {
        // This function should:
        // - call the external scanner for a_filepath
        // - set scanFilePath to a_filepath
        // - set scanFileOrigName to a_origname
        // - set scanFileIsInfected according the scan result
        // - set scanResult to the scanner output message
        // - call logScanResult() if file is infected
        // - return the scanResult, if file is infected
        // - return an empty string, if file is not infected

        $this->scanFilePath     = $a_filepath;
        $this->scanFileOrigName = $a_origname;

        // Call of scan_file from Sophie (www.vanja.com/tools/sophie)
        // sophie must run as a process
        $cmd = $this->scanCommand . " " . $a_filepath . " 2>&1";
        exec($cmd, $out, $ret);
        $this->scanResult = implode("\n", $out);

        // sophie could be called
        if ($ret == 0) {
            if (preg_match("/FILE INFECTED/", $this->scanResult)) {
                $this->scanFileIsInfected = true;
                $this->logScanResult();
                return $this->scanResult;
            } else {
                $this->scanFileIsInfected = false;
                return "";
            }
        }

        // sophie has failed (probably the daemon doesn't run)
        $this->log->write("ERROR (Virus Scanner failed): "
            . $this->scanResult
            . "; COMMAMD=" . $cmd);

        // try fallback: scan by cleaner command (sweep)
        // -ss: Don't display anything except on error or virus
        // -archive: sweep inside archives
        unset($out, $ret);
        $cmd = $this->cleanCommand . " -ss -archive " . $a_filepath . " 2>&1";
        exec($cmd, $out, $ret);
        $this->scanResult = implode("\n", $out) . " [" . $ret . "]";

        //  error codes from sweep:
        // 0  If no errors are encountered and no viruses are found.
        // 1  If  the user interrupts SWEEP (usually by pressing control-C) or kills the process.
        // 2  If some error preventing further execution is discovered.
        // 3  If viruses or virus fragments are discovered.
        if ($ret == 0) {
            $this->scanFileIsCleaned = false;
            return "";
        } elseif ($ret == 3) {
            $this->scanFileIsInfected = true;
            $this->logScanResult();
            return $this->scanResult;
        } else {
            $this->ilias->raiseError(
                $this->lng->txt("virus_scan_error") . " "
                . $this->lng->txt("virus_scan_message") . " "
                . $this->scanResult,
                $this->ilias->error_obj->WARNING
            );
        }
    }

    /**
     * clean an infected file
     * @param    string    path of file to check
     * @param    string    original name of the file to check
     * @return    string  clean message (empty if not cleaned)
     * @access    public
     */
    public function cleanFile($a_filepath, $a_origname = "")
    {
        // This function should:
        // - call the external cleaner
        // - set cleanFilePath to a_filepath
        // - set cleanFileOrigName to a_origname
        // - set cleanFileIsCleaned according the clean result
        // - set cleanResult to the cleaner output message
        // - call logCleanResult in any case
        // - return the cleanResult, if file is cleaned
        // - return an empty string, if file is not cleaned

        $this->cleanFilePath     = $a_filepath;
        $this->cleanFileOrigName = $a_origname;

        // Call of sweep from Sophos (www.sophos.com)
        // -di: Disinfect infected items
        // -nc: Don't ask for confirmation before disinfection/deletion
        // -ss: Don't display anything except on error or virus
        // -eec: Use extended error codes
        // -archive: sweep inside archives

        $cmd = $this->cleanCommand . " -di -nc -ss -eec -archive " . $a_filepath . " 2>&1";
        exec($cmd, $out, $ret);
        $this->cleanResult = implode("\n", $out) . " [" . $ret . "]";

        // always log the result from a clean attempt
        $this->logCleanResult();

        // Extended error codes from sweep:
        // 0      If no errors are encountered and no viruses are found.
        // 8      If survivable errors have occurred.
        // 12     If compressed files have been found and decompressed.
        // 16     If compressed files have been found and not decompressed.
        // 20     If viruses have been found and disinfected.
        // 24     If viruses have been found and not disinfected.
        // 28     If viruses have been found in memory.
        // 32     If there has been an integrity check failure.
        // 36     If unsurvivable errors have occurred.
        // 40     If execution has been interrupted.
        if ($ret == 20) {
            $this->cleanFileIsCleaned = true;
            return $this->cleanResult;
        } else {
            $this->cleanFileIsCleaned = false;
            return "";
        }
    }
}
