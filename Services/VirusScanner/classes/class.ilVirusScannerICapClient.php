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

class ilVirusScannerICapClient extends ilVirusScanner
{
    private const HEADER_INFECTION_FOUND = 'X-Infection-Found';

    public function __construct(string $scan_command, string $clean_command)
    {
        parent::__construct($scan_command, $clean_command);
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

    public function scanFile(string $file_path, string $org_name = ""): string
    {
        $return_string = '';
        if (file_exists($file_path)) {
            if (is_readable($file_path)) {
                $file_path     = realpath($file_path);
                $args           = ilShellUtil::escapeShellArg($file_path);
                $arguments      = $this->buildScanCommandArguments($args) . " 2>&1";
                $cmd            = ilShellUtil::escapeShellCmd($this->scanCommand);
                $out            = ilShellUtil::execQuoted($cmd, $arguments);
                $timeout        = preg_grep('/failed\/timedout.*/', $out);
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
                $return_string = sprintf('File "%s" not readable.', $file_path);
                $this->log->info($return_string);
            }
        } else {
            $return_string = sprintf('File "%s" not found.', $file_path);
            $this->log->info($return_string);
        }

        $this->log->info(sprintf('No virus found in file "%s".', $file_path));
        return $return_string;
    }
}
