<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\UI;
use ILIAS\Refinery\Transformation;

class ilUIStructureSetupAgent implements Setup\Agent
{
	/**
	 * @inheritdoc
	 */
	public function hasConfig() : bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getConfigInput(Setup\Config $config = null) : ILIAS\UI\Component\Input\Field\Input
	{
		throw new \LogicException(self::class." has no Config.");
	}

	/**
	 * @inheritdoc
	 */
	public function getArrayToConfigTransformation() : Transformation
	{
		throw new \LogicException(self::class." has no Config.");
	}

	/**
	 * @inheritdoc
	 */
	public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
	{
		return new Setup\NullObjective();
	}

	/**
	 * @inheritdoc
	 */
	public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
	{
		return new Setup\NullObjective();
	}

	/**
	 * @inheritdoc
	 */
	public function getBuildArtifactObjective() : Setup\Objective
	{
		return new \ilUIBuildDataStructureObjective();
	}
}
