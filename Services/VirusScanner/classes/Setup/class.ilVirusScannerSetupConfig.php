<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilVirusScannerSetupConfig implements Setup\Config
{
    const VIRUS_SCANNER_NONE = "none";
    const VIRUS_SCANNER_SOPHOS = "sophos";
    const VIRUS_SCANNER_ANTIVIR = "antivir";
    const VIRUS_SCANNER_CLAMAV = "clamav";

    /**
     * @var mixed
     */
    protected $virus_scanner;

    /**
     * @var string|null
     */
    protected $path_to_scan;

    /**
     * @var string|null
     */
    protected $path_to_clean;

    public function __construct(
        string $virus_scanner,
        ?string $path_to_scan,
        ?string $path_to_clean
    ) {
        $scanners = [
            self::VIRUS_SCANNER_NONE,
            self::VIRUS_SCANNER_SOPHOS,
            self::VIRUS_SCANNER_ANTIVIR,
            self::VIRUS_SCANNER_CLAMAV
        ];
        if (!in_array($virus_scanner, $scanners)) {
            throw new \InvalidArgumentException(
                "Unknown virus scanner: '$virus_scanner'"
            );
        }
        if ($virus_scanner !== self::VIRUS_SCANNER_NONE && (!$path_to_scan || !$path_to_clean)) {
            throw new \InvalidArgumentException(
                "Missing path to scan and/or clean commands for virus scanner."
            );
        }
        $this->virus_scanner = $virus_scanner;
        $this->path_to_scan = $this->toLinuxConvention($path_to_scan);
        $this->path_to_clean = $this->toLinuxConvention($path_to_clean);
    }

    protected function toLinuxConvention(?string $p) : ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getVirusScanner() : string
    {
        return $this->virus_scanner;
    }

    public function getPathToScan() : ?string
    {
        return $this->path_to_scan;
    }

    public function getPathToClean() : ?string
    {
        return $this->path_to_clean;
    }
}
