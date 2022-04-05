<?php

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilPDFGenerationSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;

    public function __construct(
        Refinery\Factory $refinery
    ) {
        $this->refinery = $refinery;
    }

    public function hasConfig() : bool
    {
        return true;
    }

    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(fn ($data) : \ilPDFGenerationSetupConfig => new \ilPDFGenerationSetupConfig(
            $data["path_to_phantom_js"] ?? null
        ));
    }

    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilPDFGenerationConfigStoredObjective($config);
    }

    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            return new ilPDFGenerationConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilPDFGenerationMetricsCollectedObjective($storage);
    }

    public function getMigrations() : array
    {
        return [];
    }
}
