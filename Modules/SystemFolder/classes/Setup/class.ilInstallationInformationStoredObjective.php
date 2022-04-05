<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

/**
 * Store information about the installation, like title, description and contact
 * information in the according fields in the ini or database.
 */
class ilInstallationInformationStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilSystemFolderSetupConfig
     */
    protected $config;

    public function __construct(
        \ilSystemFolderSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store information about installation and contact in the settings";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $common_config = $environment->getConfigFor("common");

        $settings = $factory->settingsFor("common");
        $ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $settings->set("inst_name", (string) $this->config->getClientName());
        $ini->setVariable("client", "name", $this->config->getClientName() ?? (string) $common_config->getClientId());
        $ini->setVariable("client", "description", (string) $this->config->getClientDescription());
        $settings->set("inst_institution", (string) $this->config->getClientInstitution());
        $settings->set("admin_firstname", (string) $this->config->getContactFirstname());
        $settings->set("admin_lastname", (string) $this->config->getContactLastname());
        $settings->set("admin_title", (string) $this->config->getContactTitle());
        $settings->set("admin_position", (string) $this->config->getContactPosition());
        $settings->set("admin_institution", (string) $this->config->getContactInstitution());
        $settings->set("admin_street", (string) $this->config->getContactStreet());
        $settings->set("admin_zipcode", (string) $this->config->getContactZipcode());
        $settings->set("admin_city", (string) $this->config->getContactCity());
        $settings->set("admin_country", (string) $this->config->getContactCountry());
        $settings->set("admin_phone", (string) $this->config->getContactPhone());
        $settings->set("admin_email", (string) $this->config->getContactEMail());

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write client.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $common_config = $environment->getConfigFor("common");

        $settings = $factory->settingsFor("common");
        $ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $client_name = $this->config->getClientName() ?? (string) $common_config->getClientId();

        return
            $settings->get("inst_name") !== $this->config->getClientName() ||
            $ini->readVariable("client", "name") !== $client_name ||
            $ini->readVariable("client", "description") !== $this->config->getClientDescription() ||
            $settings->get("inst_institution") !== $this->config->getClientInstitution() ||
            $settings->get("admin_firstname") !== $this->config->getContactFirstname() ||
            $settings->get("admin_lastname") !== $this->config->getContactLastname() ||
            $settings->get("admin_title") !== $this->config->getContactTitle() ||
            $settings->get("admin_position") !== $this->config->getContactPosition() ||
            $settings->get("admin_institution") !== $this->config->getContactInstitution() ||
            $settings->get("admin_street") !== $this->config->getContactStreet() ||
            $settings->get("admin_zipcode") !== $this->config->getContactZipcode() ||
            $settings->get("admin_city") !== $this->config->getContactCity() ||
            $settings->get("admin_country") !== $this->config->getContactCountry() ||
            $settings->get("admin_phone") !== $this->config->getContactPhone() ||
            $settings->get("admin_email") !== $this->config->getContactEMail()
        ;
    }
}
