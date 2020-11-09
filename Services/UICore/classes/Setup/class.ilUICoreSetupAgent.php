<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;

class ilUICoreSetupAgent implements Setup\Agent
{
    /**
     * @var ilCtrlStructureReader
     */
    protected $ctrl_reader;

    public function __construct()
    {
        $this->ctrl_reader = new \ilCtrlStructureReader();
    }

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
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new \ilCtrlStructureStoredObjective($this->ctrl_reader);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new \ilCtrlStructureStoredObjective($this->ctrl_reader, false);
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
