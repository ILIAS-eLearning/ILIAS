<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLanguagesInstalledObjective extends ilLanguageObjective {
	/**
	 * @var \ilSetupLanguage
	 */
	protected $il_setup_language;

	public function __construct(
		\ilLanguageSetupConfig $config,
		\ilSetupLanguage $il_setup_language
	) {
		parent::__construct($config);
		$this->il_setup_language = $il_setup_language;
	}

	public function getHash() : string {
		return hash(
			"sha256",
			self::class."::".
			$this->getInstallLanguagesAsString()."::".	
			$this->getInstallLocalLanguagesAsString()
		);
	}

	protected function getInstallLanguagesAsString() {
		return implode(", ", $this->config->getInstallLanguages());
	}	

	protected function getInstallLocalLanguagesAsString() {
		return implode(", ", $this->config->getInstallLocalLanguages());
	}	

	public function getLabel() : string {
		return "Install languages ".$this->getInstallLanguagesAsString();
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$db_config = $environment->getConfigFor("database");
		return [
			new ilDatabasePopulatedObjective($db_config)
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

		// TODO: Remove this once ilSetupLanguage (or a successor) supports proper
		// DI for all methods.
		$GLOBALS["ilDB"] = $db;

		$this->il_setup_language->setDbHandler($db);
		$this->il_setup_language->installLanguages(
			$this->config->getInstallLanguages(),
			$this->config->getInstallLocalLanguages()
		);

		return $environment;
	}
}
