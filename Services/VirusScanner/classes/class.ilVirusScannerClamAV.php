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

class ilVirusScannerClamAV extends ilVirusScanner
{
    private const ADD_SCAN_PARAMS = '--no-summary -i';

    public function __construct(string $scan_command, string $clean_command)
    {
        parent::__construct($scan_command, $clean_command);
        $this->type = "clamav";
        $this->scanZipFiles = true;
    }

    public function scanBuffer(string $buffer): bool
    {
        if (!$this->isBufferScanPossible()) {
            return $this->scanFileFromBuffer($buffer);
        }

        return $this->processBufferScan($buffer);
    }

    protected function isBufferScanPossible(): bool
    {
        $functions = ['proc_open', 'proc_close'];

        foreach ($functions as $func) {
            if (function_exists($func)) {
                continue;
            }

            return false;
        }

        return true;
    }

    protected function processBufferScan(string $buffer): bool
    {
        $descriptor_spec = [
            0 => ["pipe", "r"],  // stdin is a pipe that the child will read from
            1 => ["pipe", "w"],  // stdout is a pipe that the child will write to
            2 => ["pipe", "w"]        // stderr for the child
        ];

        $pipes = []; // will look like follows after passing
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

        return (bool) $this->hasDetections($detectionReport);
    }

    protected function buildScanCommand(string $file = '-'): string
    {
        return $this->scanCommand . ' ' . self::ADD_SCAN_PARAMS . ' ' . $file;
    }

    protected function hasDetections(string $detectionReport): int
    {
        return preg_match("/FOUND/", $detectionReport);
    }

    public function scanFile(string $file_path, string $org_name = ""): string
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
        }

        $this->scanFileIsInfected = false;
        return "";
    }
}
