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

    /**
     * ilVirusScannerICapClient constructor.
     * @param $a_scancommand
     * @param $a_cleancommand
     */
    public function __construct($a_scancommand, $a_cleancommand)
    {
        parent::__construct($a_scancommand, $a_cleancommand);
        $this->scanCommand = IL_ICAP_CLIENT;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function buildScanCommandArguments($file = '-') // default means piping
    {
        return ' -i ' . IL_ICAP_HOST . ' -p ' . IL_ICAP_PORT . ' -v -s ' . IL_ICAP_AV_COMMAND . ' -f ' . $file;
    }

    /**
     * @param        $a_filepath
     * @param string $a_origname
     * @return bool|string
     */
    function scanFile($a_filepath, $a_origname = "")
    {
        $return_string = '';
        if (file_exists($a_filepath)) {
            if (is_readable($a_filepath)) {
                $a_filepath     = realpath($a_filepath);
                $args           = ilUtil::escapeShellArg($a_filepath);
                $arguments      = $this->buildScanCommandArguments($args) . " 2>&1";
                $cmd            = ilUtil::escapeShellCmd($this->scanCommand);
                $out            = ilUtil::execQuoted($cmd, $arguments);
                $timeout        = preg_grep('/failed\/timedout.*/', $out);
                $virus_detected = preg_grep('/' . self::HEADER_INFECTION_FOUND . '.*/', $out);
                if (is_array($virus_detected) && count($virus_detected) > 0) {
                    $return_string = sprintf('Virus detected in %s', $a_filepath);
                    $this->log->warning($return_string);
                    
                } elseif (is_array($timeout) && count($timeout) > 0) {
                    $return_string = 'Cannot connect to icap server.';
                    $this->log->warning($return_string);
                }
                $this->scanResult = implode("\n", $out);
            } else {
                $return_string = sprintf('File "%s" not readable.', $a_filepath);
                $this->log->info($return_string);
            }
        } else {
            $return_string = sprintf('File "%s" not found.', $a_filepath);
            $this->log->info($return_string);
        }
        $this->log->info(sprintf('No virus found in file "%s".', $a_filepath));
        return $return_string;
    }
}
