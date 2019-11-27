<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilMathJaxConfigStoredObjective implements Setup\Objective {
	/**
	 * @var	\ilMathJaxSetupConfig
	 */
	protected $config;

	public function __construct(
		\ilMathJaxSetupConfig $config
	) {
		$this->config = $config;
	}

	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "Store configuration of Services/MathJax";
	}

	public function isNotable() : bool {
		return false;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$common_config = $environment->getConfigFor("common");
		return [
			new ilIniFilesPopulatedObjective($common_config)
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

		$ini->setVariable("tools", "latex", $this->config->getPathToLatexCGI());

		if (!$ini->write()) {
			throw new Setup\UnachievableException("Could not write ilias.ini.php");
		}

		return $environment;
	}
}
