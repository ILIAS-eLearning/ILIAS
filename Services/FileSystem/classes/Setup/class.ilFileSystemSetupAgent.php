<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilFileSystemSetupAgent implements Setup\Agent {
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
			return new \ilFileSystemSetupConfig(
				$data["data_dir"]
			);
		});	
	}

	/**
	 * @inheritdoc
	 */
	public function getInstallObjective(Setup\Config $config = null) : Setup\Objective {
		return new Setup\ObjectiveCollection(
			"Services/FileSystem objectives.",
			false,
			new ilFileSystemSetIniObjective($config)
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
