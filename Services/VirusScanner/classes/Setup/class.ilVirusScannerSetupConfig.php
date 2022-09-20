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

use ILIAS\Setup;

class ilVirusScannerSetupConfig implements Setup\Config
{
    public const VIRUS_SCANNER_NONE = "none";
    private const VIRUS_SCANNER_SOPHOS = "sophos";
    private const VIRUS_SCANNER_ANTIVIR = "antivir";
    private const VIRUS_SCANNER_CLAMAV = "clamav";
    private const VIRUS_SCANNER_ICAP = "icap";

    protected string $virus_scanner;

    protected ?string $path_to_scan = null;

    protected ?string $path_to_clean = null;

    protected ?string $icap_host = null;

    protected ?string $icap_port = null;

    protected ?string $icap_service_name = null;

    protected ?string $icap_client_path = null;

    public function __construct(
        string $virus_scanner,
        ?string $path_to_scan,
        ?string $path_to_clean,
        ?string $icap_host,
        ?string $icap_port,
        ?string $icap_service_name,
        ?string $icap_client_path
    ) {
        $scanners = [
            self::VIRUS_SCANNER_NONE,
            self::VIRUS_SCANNER_SOPHOS,
            self::VIRUS_SCANNER_ANTIVIR,
            self::VIRUS_SCANNER_CLAMAV,
            self::VIRUS_SCANNER_ICAP
        ];
        if (!in_array($virus_scanner, $scanners)) {
            throw new InvalidArgumentException(
                "Unknown virus scanner: '$virus_scanner'"
            );
        }
        if ($virus_scanner === self::VIRUS_SCANNER_ICAP) {
            $this->icap_host = $this->toLinuxConvention($icap_host);
            $this->icap_port = $this->toLinuxConvention($icap_port);
            $this->icap_service_name = $this->toLinuxConvention($icap_service_name);
            $this->icap_client_path = $this->toLinuxConvention($icap_client_path);
        } elseif ($virus_scanner !== self::VIRUS_SCANNER_NONE && (!$path_to_scan || !$path_to_clean)) {
            throw new InvalidArgumentException(
                "Missing path to scan and/or clean commands for virus scanner."
            );
        }
        $this->virus_scanner = $virus_scanner;
        $this->path_to_scan = $this->toLinuxConvention($path_to_scan);
        $this->path_to_clean = $this->toLinuxConvention($path_to_clean);
    }

    protected function toLinuxConvention(?string $p): ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getVirusScanner(): string
    {
        return $this->virus_scanner;
    }

    public function getPathToScan(): ?string
    {
        return $this->path_to_scan;
    }

    public function getPathToClean(): ?string
    {
        return $this->path_to_clean;
    }

    public function getIcapHost(): ?string
    {
        return $this->icap_host;
    }

    public function getIcapPort(): ?string
    {
        return $this->icap_port;
    }

    public function getIcapServiceName(): ?string
    {
        return $this->icap_service_name;
    }

    public function getIcapClientPath(): ?string
    {
        return $this->icap_client_path;
    }
}
