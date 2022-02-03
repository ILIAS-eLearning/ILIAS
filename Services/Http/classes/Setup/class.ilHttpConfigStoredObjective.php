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
class ilHttpConfigStoredObjective implements Setup\Objective
{
    protected \ilHttpSetupConfig $config;

    public function __construct(
        \ilHttpSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store configuration of Services/Http";
    }

    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @return \ilIniFilesLoadedObjective[]|\ilSettingsFactoryExistsObjective[]
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable(
            ilHTTPS::SETTINGS_GROUP_SERVER,
            ilHTTPS::SETTING_HTTP_PATH,
            $this->config->getHttpPath()
        );
        $ini->setVariable(
            ilHTTPS::SETTINGS_GROUP_HTTPS,
            ilHTTPS::SETTING_FORCED,
            $this->config->isForced() ? "1" : "0"
        );
        $ini->setVariable(
            ilHTTPS::SETTINGS_GROUP_HTTPS,
            ilHTTPS::SETTING_AUTO_HTTPS_DETECT_ENABLED,
            $this->config->isAutodetectionEnabled() ? "1" : "0"
        );
        $ini->setVariable(
            ilHTTPS::SETTINGS_GROUP_HTTPS,
            ilHTTPS::SETTING_AUTO_HTTPS_DETECT_HEADER_NAME,
            (string) $this->config->getHeaderName()
        );
        $ini->setVariable(
            ilHTTPS::SETTINGS_GROUP_HTTPS,
            ilHTTPS::SETTING_AUTO_HTTPS_DETECT_HEADER_VALUE,
            (string) $this->config->getHeaderValue()
        );


        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");
        $settings->set("proxy_status", (string) $this->config->isProxyEnabled());
        $settings->set("proxy_host", (string) $this->config->getProxyHost());
        $settings->set("proxy_port", (string) $this->config->getProxyPort());

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");

        $detect_enabled = $this->config->isAutodetectionEnabled() ? "1" : "0";
        $forced = $this->config->isForced() ? "1" : "0";

        return
            $ini->readVariable(ilHTTPS::SETTINGS_GROUP_SERVER, ilHTTPS::SETTING_HTTP_PATH) !== $this->config->getHttpPath() ||
            $ini->readVariable(ilHTTPS::SETTINGS_GROUP_HTTPS, ilHTTPS::SETTING_AUTO_HTTPS_DETECT_ENABLED) !== $detect_enabled ||
            $ini->readVariable(ilHTTPS::SETTINGS_GROUP_HTTPS, ilHTTPS::SETTING_FORCED) !== $forced ||
            $ini->readVariable(ilHTTPS::SETTINGS_GROUP_HTTPS, ilHTTPS::SETTING_AUTO_HTTPS_DETECT_HEADER_NAME) !== $this->config->getHeaderName() ||
            $ini->readVariable(ilHTTPS::SETTINGS_GROUP_HTTPS, ilHTTPS::SETTING_AUTO_HTTPS_DETECT_HEADER_VALUE) !== $this->config->getHeaderValue() ||
            $settings->get("proxy_status") !== (int) $this->config->isProxyEnabled() ||
            $settings->get("proxy_host") !== $this->config->getProxyHost() ||
            $settings->get("proxy_port") !== $this->config->getProxyPort()
        ;
    }
}
