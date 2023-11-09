<?php

declare(strict_types=1);

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
class ilHttpSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        Refinery\Factory $refinery
    ) {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data): \ilHttpSetupConfig {
            return new \ilHttpSetupConfig(
                $data["path"],
                isset($data["https_autodetection"]) && $data["https_autodetection"],
                isset($data["forced"]) && $data["forced"],
                (isset($data["https_autodetection"]) && $data["https_autodetection"])
                    ? $data["https_autodetection"]["header_name"]
                    : null,
                (isset($data["https_autodetection"]) && $data["https_autodetection"])
                    ? $data["https_autodetection"]["header_value"]
                    : null,
                isset($data["proxy"]) && $data["proxy"],
                (isset($data["proxy"]) && $data["proxy"])
                    ? $data["proxy"]["host"]
                    : null,
                (isset($data["proxy"]) && $data["proxy"])
                    ? $data["proxy"]["port"]
                    : null,
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        $http_config_stored = new ilHttpConfigStoredObjective($config);

        if (!$config->isProxyEnabled()) {
            return $http_config_stored;
        }

        return new Setup\Objective\ObjectiveWithPreconditions(
            $http_config_stored,
            new ProxyConnectableCondition($config)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        if ($config !== null) {
            return new ilHttpConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilHttpMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
