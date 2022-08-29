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

    public ilErrorHandling $error;

    public ilLanguage $lng;

    public ilLogger $log;

    public function __construct(string $scan_command, string $clean_command)
    {
        global $DIC;
        $error = $DIC['ilErr'];
        $lng = $DIC->language();
        $log = $DIC->logger()->root();

        $this->error = $error;
        $this->lng = $lng;
        $this->log = $log;
        $this->scanCommand = $scan_command;
        $this->cleanCommand = $clean_command;

        $this->type = "simulate";
        $this->scanZipFiles = false;
    }

    /**
     * @param string $a_file
     * @param string $a_orig_name
     * @param bool $a_clean
     * @return array{0: bool, 1: string}
     */
    public static function virusHandling(string $a_file, string $a_orig_name = '', bool $a_clean = true): array
    {
        global $DIC;

        $lng = $DIC->language();

        if ((defined('IL_VIRUS_SCANNER') && IL_VIRUS_SCANNER !== 'None') || (defined(
            'IL_ICAP_HOST'
        ) && IL_ICAP_HOST !== '')) {
            $vs = ilVirusScannerFactory::_getInstance();
            if (($vs_txt = $vs->scanFile($a_file, $a_orig_name)) !== '') {
                if ($a_clean && defined('IL_VIRUS_CLEAN_COMMAND') && IL_VIRUS_CLEAN_COMMAND !== '') {
                    $clean_txt = $vs->cleanFile($a_file, $a_orig_name);
                    if ($vs->fileCleaned()) {
                        $vs_txt .= '<br />' . $lng->txt('cleaned_file') . '<br />' . $clean_txt;
                        $vs_txt .= '<br />' . $lng->txt('repeat_scan');
                        if (($vs2_txt = $vs->scanFile($a_file, $a_orig_name)) !== '') {
                            return [
                                false,
                                nl2br($vs_txt) . '<br />' . $lng->txt('repeat_scan_failed') . '<br />' . nl2br($vs2_txt)
                            ];
                        }

                        return [true, nl2br($vs_txt) . '<br />' . $lng->txt('repeat_scan_succeded')];
                    }

                    return [false, nl2br($vs_txt) . '<br />' . $lng->txt('cleaning_failed')];
                }

                return [false, nl2br($vs_txt)];
            }
        }

        return [true, ''];
    }

    public function scanBuffer(string $buffer): bool
    {
        return $this->scanFileFromBuffer($buffer);
    }

    protected function scanFileFromBuffer(string $buffer): bool
    {
        $bufferFile = $this->createBufferFile($buffer);
        $infection = $this->scanFile($bufferFile);
        $this->removeBufferFile($bufferFile);
        return $infection !== '';
    }

    protected function createBufferFile(string $buffer): string
    {
        $bufferFile = ilFileUtils::ilTempnam();
        file_put_contents($bufferFile, $buffer);
        return $bufferFile;
    }

    public function scanFile(string $file_path, string $org_name = ""): string
    {
        $this->scanFilePath = $file_path;
        $this->scanFileOrigName = $org_name;

        if ($org_name === "infected.txt" || $org_name === "cleanable.txt") {
            $this->scanFileIsInfected = true;
            $this->scanResult =
                "FILE INFECTED: [" . $file_path . "] (VIRUS: simulated)";
            $this->logScanResult();
            return $this->scanResult;
        }

        $this->scanFileIsInfected = false;
        $this->scanResult = "";
        return "";
    }

    public function logScanResult(): void
    {
        $mess = "Virus Scanner (" . $this->type . ")";
        if ($this->scanFileOrigName) {
            $mess .= " (File " . $this->scanFileOrigName . ")";
        }
        $mess .= ": " . preg_replace('/[\r\n]+/', "; ", $this->scanResult);

        $this->log->write($mess);
    }

    protected function removeBufferFile(string $bufferFile): void
    {
        unlink($bufferFile);
    }

    public function cleanFile(string $file_path, string $org_name = ""): string
    {
        $this->cleanFilePath = $file_path;
        $this->cleanFileOrigName = $org_name;

        if ($org_name === "cleanable.txt") {
            $this->cleanFileIsCleaned = true;
            $this->cleanResult =
                "FILE CLEANED: [" . $file_path . "] (VIRUS: simulated)";
            $this->logCleanResult();
            return $this->cleanResult;
        }

        $this->cleanFileIsCleaned = false;
        $this->cleanResult =
            "FILE NOT CLEANED: [" . $file_path . "] (VIRUS: simulated)";
        $this->logCleanResult();
        return "";
    }

    public function logCleanResult(): void
    {
        $mess = "Virus Cleaner (" . $this->type . ")";
        if ($this->cleanFileOrigName) {
            $mess .= " (File " . $this->cleanFileOrigName . ")";
        }
        $mess .= ": " . preg_replace('/[\r\n]+/', "; ", $this->cleanResult);

        $this->log->write($mess);
    }

    public function fileCleaned(): bool
    {
        return $this->cleanFileIsCleaned;
    }

    public function getScanResult(): string
    {
        return $this->scanResult;
    }

    public function getCleanResult(): string
    {
        return $this->cleanResult;
    }

    public function getScanMessage(): string
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

    public function getCleanMessage(): string
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

    public function getScanZipFiles(): bool
    {
        return $this->scanZipFiles;
    }
}
