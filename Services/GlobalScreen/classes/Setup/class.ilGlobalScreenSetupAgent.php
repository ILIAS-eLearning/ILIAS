<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ilGlobalScreenSetupAgent implements Setup\Agent {
	/**
	 * @var Refinery
	 */
	protected $refinery;

	public function __construct(Refinery $refinery) {
		$this->refinery = $refinery;
	}

	/**
	 * @inheritdocs
	 */
	public function hasConfig() : bool {
		return false;
	}

	/**
	 * @inheritdocs
	 */
	public function getConfigInput(Setup\Config $config = null) : ILIAS\UI\Component\Input\Field\Input {
		throw new \LogicException(self::class." has no Config.");
	}

	/**
	 * @inheritdocs
	 */
	public function getArrayToConfigTransformation() : Transformation {
		throw new \LogicException(self::class." has no Config.");
	}

	/**
	 * @inheritdocs
	 */
	public function getInstallObjective(Setup\Config $config = null) : Setup\Objective {
		return new Setup\NullObjective();
	}

	/**
	 * @inheritdocs
	 */
	public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective {
		return new Setup\NullObjective();
	}

	/**
	 * @inheritdocs
	 */
	public function getBuildArtifactObjective() : Setup\Objective {
		return new \ilGlobalScreenBuildProviderMapObjective();
	}
}
