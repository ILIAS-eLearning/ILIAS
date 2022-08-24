<?php

declare(strict_types=1);

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

class ilVirusScannerSophos extends ilVirusScanner
{
    public function __construct(string $scan_command, string $clean_command)
    {
        parent::__construct($scan_command, $clean_command);
        $this->type = "sophos";
        $this->scanZipFiles = true;
    }

    public function scanFile(string $file_path, string $org_name = ""): string
    {
        $this->scanFilePath = $file_path;
        $this->scanFileOrigName = $org_name;

        // Call of scan_file from Sophie (www.vanja.com/tools/sophie)
        // sophie must run as a process
        $cmd = $this->scanCommand . " " . $file_path . " 2>&1";
        exec($cmd, $out, $ret);
        $this->scanResult = implode("\n", $out);

        // sophie could be called
        if ((int) $ret === 0) {
            if (preg_match("/FILE INFECTED/", $this->scanResult)) {
                $this->scanFileIsInfected = true;
                $this->logScanResult();
                return $this->scanResult;
            }

            $this->scanFileIsInfected = false;
            return "";
        }

        // sophie has failed (probably the daemon doesn't run)
        $this->log->write("ERROR (Virus Scanner failed): "
            . $this->scanResult
            . "; COMMAMD=" . $cmd);

        // try fallback: scan by cleaner command (sweep)
        // -ss: Don't display anything except on error or virus
        // -archive: sweep inside archives
        unset($out, $ret);
        $cmd = $this->cleanCommand . " -ss -archive " . $file_path . " 2>&1";
        exec($cmd, $out, $ret);
        $this->scanResult = implode("\n", $out) . " [" . $ret . "]";

        //  error codes from sweep:
        // 0  If no errors are encountered and no viruses are found.
        // 1  If  the user interrupts SWEEP (usually by pressing control-C) or kills the process.
        // 2  If some error preventing further execution is discovered.
        // 3  If viruses or virus fragments are discovered.
        if ((int) $ret === 0) {
            $this->cleanFileIsCleaned = false;
            return "";
        } elseif ((int) $ret === 3) {
            $this->scanFileIsInfected = true;
            $this->logScanResult();
            return $this->scanResult;
        }

        $this->error->raiseError(
            $this->lng->txt("virus_scan_error") . " "
            . $this->lng->txt("virus_scan_message") . " "
            . $this->scanResult,
            $this->error->WARNING
        );
    }

    public function cleanFile(string $file_path, string $org_name = ""): string
    {
        $this->cleanFilePath = $file_path;
        $this->cleanFileOrigName = $org_name;

        // Call of sweep from Sophos (www.sophos.com)
        // -di: Disinfect infected items
        // -nc: Don't ask for confirmation before disinfection/deletion
        // -ss: Don't display anything except on error or virus
        // -eec: Use extended error codes
        // -archive: sweep inside archives

        $cmd = $this->cleanCommand . " -di -nc -ss -eec -archive " . $file_path . " 2>&1";
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
        if ((int) $ret === 20) {
            $this->cleanFileIsCleaned = true;
            return $this->cleanResult;
        }

        $this->cleanFileIsCleaned = false;
        return "";
    }
}
