<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface to the AntiVir virus protector
 * @author    Alex Killing <alex.killing@gmx.de>
 * @version   $Id$
 * @extends   ilVirusScanner
 * @deprecated since ILIAS 7.0, the last update for the virus database was 2016
 */

require_once "Services/VirusScanner/classes/class.ilVirusScanner.php";

class ilVirusScannerAntiVir extends ilVirusScanner
{
    public function __construct(string $scan_command, string $clean_command)
    {
        parent::__construct($scan_command, $clean_command);
        $this->type = "antivir";
        $this->scanZipFiles = true;
    }

    public function scanFile(string $file_path, string $org_name = "") : string
    {
        $this->scanFilePath = $file_path;
        $this->scanFileOrigName = $org_name;

        // Call of antivir command
        $cmd = $this->scanCommand . " " . $file_path . " ";
        exec($cmd, $out, $ret);
        $this->scanResult = implode("\n", $out);

        // sophie could be called
        if (preg_match('/ALERT:/', $this->scanResult)) {
            $this->scanFileIsInfected = true;
            $this->logScanResult();
            return $this->scanResult;
        } else {
            $this->scanFileIsInfected = false;
            return "";
        }
    }
}
