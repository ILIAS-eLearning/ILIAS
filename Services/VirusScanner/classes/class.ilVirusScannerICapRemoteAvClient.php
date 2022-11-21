<?php
//hsuhh-patch: begin
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilVirusScannerICapRemoteAvClient extends ilVirusScannerICapRemote
{
    const HEADER = 'headers';
    const HEADER_VIOLATION_FOUND = 'X-Violations-Found';
    const HEADER_INFECTION_FOUND = 'X-Infection-Found';

    /**
     * ilVirusScannerICapAvClient constructor.
     * @param $a_scancommand
     * @param $a_cleancommand
     */
    public function __construct($a_scancommand, $a_cleancommand)
    {
        parent::__construct($a_scancommand, $a_cleancommand);
        $this->options(IL_ICAP_AV_COMMAND);
    }

    /**
     * @param        $a_filepath
     * @param string $a_origname
     * @return bool|string
     */
    public function scanFile($a_filepath, $a_origname = "")
    {
        $return_string = '';
        if (file_exists($a_filepath)) {
            if (is_readable($a_filepath)) {
                $a_filepath = realpath($a_filepath);
                $results = ($this->reqmod(
                    'avscan',
                    [
                        'req-hdr'  => "POST /test HTTP/1.1\r\nHost: 127.0.0.1\r\n\r\n",
                        'req-body' => file_get_contents($a_filepath) //Todo: find a better way
                    ]
                ));
                if ($this->analyseHeader($results)) {
                    $return_string = sprintf('Virus found in file "%s"!', $a_filepath);
                    $this->log->warning($return_string);
                }
            } else {
                $return_string = sprintf('File "%s" not readable.', $a_filepath);
                $this->log->warning($return_string);
            }
        } else {
            $return_string = sprintf('File "%s" not found.', $a_filepath);
            $this->log->warning($return_string);
        }
        $this->log->info(sprintf('No virus found in file "%s".', $a_filepath));
        return $return_string;
    }

    /**
     * @param $header
     * @return bool
     */
    protected function analyseHeader($header)
    {
        $virus_found = false;
        if (array_key_exists(self::HEADER, $header)) {
            $header = $header[self::HEADER];
            if (array_key_exists(self::HEADER_VIOLATION_FOUND, $header)) {
                if ($header[self::HEADER_VIOLATION_FOUND] > 0) {
                    $virus_found = true;
                }
            }
            if (array_key_exists(self::HEADER_INFECTION_FOUND, $header)) {
                if (strlen($header[self::HEADER_INFECTION_FOUND]) > 0) {
                    $infection_split = preg_split('/;/', $header[self::HEADER_INFECTION_FOUND]);
                    foreach ($infection_split as $infection) {
                        $parts = preg_split('/=/', $infection);
                        if ($parts !== false &&
                            is_array($parts) &&
                            count($parts) > 0 &&
                            strlen($parts[0]) > 0) {
                            $this->log->warning(trim($parts[0]) . ': ' . trim($parts[1]));
                        }
                    }
                    $virus_found = true;
                }
            }
        }
        return $virus_found;
    }
}
//hsuhh-patch: end