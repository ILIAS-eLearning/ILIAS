<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

/**
 * Store information about the installation, like title, description and contact
 * information in the according fields in the ini or database.
 */
class ilInstallationInformationStoredObjective implements Setup\Objective {
	/**
	 * @var	\ilSystemFolderSetupConfig
	 */
	protected $config;

	public function __construct(
		\ilSystemFolderSetupConfig $config
	) {
		$this->config = $config;
	}

	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "Store information about installation and contact in the settings";
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$common_config = $environment->getConfigFor("common");
		return [
			new \ilIniFilesPopulatedObjective($common_config),
			new \ilSettingsFactoryExistsObjective()
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
		$common_config = $environment->getConfigFor("common");

		$settings = $factory->settingsFor("common");
		$ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

		$settings->set("inst_name", $this->config->getClientName());
		$ini->setVariable("client", "name", $this->config->getClientName() ?? $common_config->getClientId());
		$ini->setVariable("client", "description", $this->config->getClientDescription());
		$settings->set("inst_institution", $this->config->getClientInstitution());
		$settings->set("admin_firstname", $this->config->getContactFirstname());
		$settings->set("admin_lastname", $this->config->getContactLastname());
		$settings->set("admin_title", $this->config->getContactTitle());
		$settings->set("admin_position", $this->config->getContactPosition());
		$settings->set("admin_institution", $this->config->getContactInstitution());
		$settings->set("admin_street", $this->config->getContactStreet());
		$settings->set("admin_zipcode", $this->config->getContactZipcode());
		$settings->set("admin_city", $this->config->getContactCity());
		$settings->set("admin_country", $this->config->getContactCountry());
		$settings->set("admin_phone", $this->config->getContactPhone());
		$settings->set("admin_email", $this->config->getContactEMail());

		if (!$ini->write()) {
			throw new Setup\UnachievableException("Could not write client.ini.php");
		}

		return $environment;
	}
}
