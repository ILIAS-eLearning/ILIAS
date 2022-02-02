<?php

use ILIAS\Setup;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilVirusScannerConfigStoredObjective implements Setup\Objective
{
    protected \ilVirusScannerSetupConfig $config;

    public function __construct(
        ilVirusScannerSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with settings for Services/VirusScanner";
    }

    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @return \ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable("tools", "vscantype", $this->config->getVirusScanner());
        $ini->setVariable("tools", "scancommand", $this->config->getPathToScan());
        $ini->setVariable("tools", "cleancommand", $this->config->getPathToClean());
        $ini->setVariable("tools", "icap_host", $this->config->getIcapHost());
        $ini->setVariable("tools", "icap_port", $this->config->getIcapPort());
        $ini->setVariable("tools", "icap_service_name", $this->config->getIcapServiceName());
        $ini->setVariable("tools", "icap_client_path", $this->config->getIcapClientPath());

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return
            $ini->readVariable("tools", "vscantype") !== $this->config->getVirusScanner() ||
            $ini->readVariable("tools", "scancommand") !== $this->config->getPathToScan() ||
            $ini->readVariable("tools", "cleancommand") !== $this->config->getPathToClean() ||
            $ini->readVariable("tools", "icap_host") !== $this->config->getIcapHost() ||
            $ini->readVariable("tools", "icap_port") !== $this->config->getIcapPort() ||
            $ini->readVariable("tools", "icap_service_name") !== $this->config->getIcapServiceName() ||
            $ini->readVariable("tools", "icap_client_path") !== $this->config->getIcapClientPath();
    }
}
