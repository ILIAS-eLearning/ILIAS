<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery;
use ILIAS\Refinery\Factory;
use ILIAS\Setup;

class ilVirusScannerSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Factory $refinery;

    public function __construct(
        Factory $refinery
    ) {
        $this->refinery = $refinery;
    }

    public function hasConfig() : bool
    {
        return true;
    }

    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(fn ($data) : ilVirusScannerSetupConfig => new ilVirusScannerSetupConfig(
            $data["virusscanner"] ?? ilVirusScannerSetupConfig::VIRUS_SCANNER_NONE,
            $data["path_to_scan"] ?? null,
            $data["path_to_clean"] ?? null,
            $data["icap_host"] ?? null,
            $data["icap_port"] ?? null,
            $data["icap_service_name"] ?? null,
            $data["icap_client_path"] ?? null,
        ));
    }

    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilVirusScannerConfigStoredObjective($config);
    }

    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            return new ilVirusScannerConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilVirusScannerMetricsCollectedObjective($storage);
    }

    public function getMigrations() : array
    {
        return [];
    }
}
