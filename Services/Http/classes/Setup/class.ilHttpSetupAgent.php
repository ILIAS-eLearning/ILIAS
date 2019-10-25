<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilHttpSetupAgent implements Setup\Agent {
	/**
	 * @var Refinery\Factory
	 */
	protected $refinery;

	public function __construct(
		Refinery\Factory $refinery
	) {
		$this->refinery = $refinery;
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
			return new \ilHttpSetupConfig(
				$data["http_path"],
				$data["https_autodetection"],
				$data["https_header_name"],
				$data["https_header_value"]	
			);
		});	
	}

	/**
	 * @inheritdoc
	 */
	public function getInstallObjective(Setup\Config $config = null) : Setup\Objective {
		return new Setup\ObjectiveCollection(
			"Services/Http objectives.",
			false,
			new ilHttpSetIniObjective($config)
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
