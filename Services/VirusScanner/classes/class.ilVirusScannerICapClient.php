<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface to the ClamAV virus protector
 * @author        Ralf Schenk <rs@databay.de>
 * @version       $Id$
 * @extends       ilVirusScanner
 */

require_once "./Services/VirusScanner/classes/class.ilVirusScanner.php";

class ilVirusScannerICapClient extends ilVirusScanner
{
    const HEADER_INFECTION_FOUND = 'X-Infection-Found';

    public function __construct(string $scan_command, string $clean_command)
    {
        parent::__construct($scan_command, $clean_command);
        $this->scanCommand = IL_ICAP_CLIENT;
    }

    protected function buildScanCommand(string $file = '-') : string// default means piping
    {
        return $this->scanCommand . ' -i ' . IL_ICAP_HOST . ' -p ' . IL_ICAP_PORT . ' -v -s ' . IL_ICAP_AV_COMMAND . ' -f ' . $file;
    }

    public function scanFile(string $file_path, string $org_name = "") : string
    {
        $return_string = '';
        if (is_readable($file_path)) {
            $cmd = $this->buildScanCommand($file_path) . " 2>&1";
            $out = ilUtil::execQuoted($cmd);
            $timeout = preg_grep('/failed\/timedout.*/', $out);
            $virus_detected = preg_grep('/' . self::HEADER_INFECTION_FOUND . '.*/', $out);
            if (is_array($virus_detected) && count($virus_detected) > 0) {
                $return_string = sprintf('Virus detected in %s', $file_path);
                $this->log->warning($return_string);
            } elseif (is_array($timeout) && count($timeout) > 0) {
                $return_string = 'Cannot connect to icap server.';
                $this->log->warning($return_string);
            }
            $this->scanResult = implode("\n", $out);
        } else {
            $return_string = sprintf('File "%s" not found or not readable.', $file_path);
            $this->log->info($return_string);
        }

        $this->log->info(sprintf('No virus found in file "%s".', $file_path));
        return $return_string;
    }
}
