<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilLanguageSetupAgent implements Setup\Agent {
	/**
	 * @var Refinery\Factory
	 */
	protected $refinery;

	/**
	 * @var \ilSetupLanguage
	 */
	protected $il_setup_language;

	public function __construct(
		Refinery\Factory $refinery,
		\ilSetupLanguage $il_setup_language
	) {
		$this->refinery = $refinery;
		$this->il_setup_language = $il_setup_language;
	}

	/**
	 * @inheritdoc
	 */
	public function hasConfig() : bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getConfigInput(Setup\Config $config = null) : UI\Component\Input\Field\Input {
		throw new \LogicException("Not yet implemented.");
	}

	/**
	 * @inheritdoc
	 */
	public function getArrayToConfigTransformation() : Refinery\Transformation {
		return $this->refinery->custom()->transformation(function($data) {
			return new \ilLanguageSetupConfig(
				$data["default_language"],
				$data["install_languages"] ?? [$data["default_language"]],
				$data["install_local_languages"] ?? []
			);
		});	
	}

	/**
	 * @inheritdoc
	 */
	public function getInstallObjective(Setup\Config $config = null) : Setup\Objective {
		return new Setup\ObjectiveCollection(
			"Complete objectives from Services/Language",
			false,
			new ilLanguageConfigStoredObjective($config),
			new ilLanguagesInstalledObjective($config, $this->il_setup_language),
			new ilDefaultLanguageSetObjective($config)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective {
		return new Setup\NullObjective();
	}

	/**
	 * @inheritdoc
	 */
	public function getBuildArtifactObjective() : Setup\Objective {
		return new Setup\NullObjective();
	}
}
