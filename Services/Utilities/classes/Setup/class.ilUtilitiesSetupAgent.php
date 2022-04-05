<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;

class ilUtilitiesSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;
    
    public function __construct(Refinery\Factory $refinery)
    {
        $this->refinery = $refinery;
    }
    
    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new ilUtilitiesSetupConfig(
                $data["path_to_convert"] ?? "/usr/bin/convert",
                $data["path_to_zip"] ?? "/usr/bin/zip",
                $data["path_to_unzip"] ?? "/usr/bin/unzip"
            );
        });
    }
    
    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        /** @var ilUtilitiesSetupConfig $config */
        return new ilUtilitiesConfigStoredObjective($config);
    }
    
    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            /** @var ilUtilitiesSetupConfig $config */
            return new ilUtilitiesConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
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
        return new ilUtilitiesMetricsCollectedObjective($storage);
    }
    
    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
