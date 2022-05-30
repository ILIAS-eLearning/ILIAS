<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Config;

/**
 * Contains common objectives for the setup. Do not make additions here, in
 * general all this stuff here is supposed to go elsewhere once we find out
 * which service it really belongs to.
 */
class ilSetupAgent implements Setup\Agent
{
    const PHP_MEMORY_LIMIT = "128M";

    protected Refinery\Factory $refinery;
    protected Data\Factory $data;

    public function __construct(
        Refinery\Factory $refinery,
        Data\Factory $data
    ) {
        $this->refinery = $refinery;
        $this->data = $data;
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
            $datetimezone = $this->refinery->to()->toNew(\DateTimeZone::class);
            return new \ilSetupConfig(
                $this->data->clientId($data["client_id"] ?? ''),
                $datetimezone->transform([$data["server_timezone"] ?? "UTC"]),
                $data["register_nic"] ?? false
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\Objective\ObjectiveWithPreconditions(
            new \ilMakeInstallationAccessibleObjective($config),
            new \ilOverwritesExistingInstallationConfirmed($config),
            new Setup\ObjectiveCollection(
                "Complete common ILIAS objectives.",
                false,
                new Setup\Condition\PHPVersionCondition("7.2.0"),
                new Setup\Condition\PHPExtensionLoadedCondition("dom"),
                new Setup\Condition\PHPExtensionLoadedCondition("xsl"),
                new Setup\Condition\PHPExtensionLoadedCondition("gd"),
                $this->getPHPMemoryLimitCondition(),
                new ilSetupConfigStoredObjective($config),
                $config->getRegisterNIC()
                    ? new ilNICKeyRegisteredObjective($config)
                    : new Setup\ObjectiveCollection(
                        "",
                        false,
                        new ilNICKeyStoredObjective($config),
                        new ilInstIdDefaultStoredObjective($config)
                    )
            )
        );
    }

    protected function getPHPMemoryLimitCondition() : Setup\Objective
    {
        return new Setup\Condition\ExternalConditionObjective(
            "PHP memory limit >= " . self::PHP_MEMORY_LIMIT,
            function (Setup\Environment $env) : bool {
                $limit = ini_get("memory_limit");
                if ($limit == -1) {
                    return true;
                }
                $expected = $this->data->dataSize(self::PHP_MEMORY_LIMIT);
                $current = $this->data->dataSize($limit);
                return $current->inBytes() >= $expected->inBytes();
            },
            "To properly execute ILIAS, please take care that the PHP memory limit is at least set to 128M."
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        $objectives = [
            new Setup\Objective\ObjectiveWithPreconditions(
                new ilVersionWrittenToSettingsObjective($this->data),
                new ilNoMajorVersionSkippedConditionObjective($this->data),
                new ilNoVersionDowngradeConditionObjective($this->data)
            )
        ];
        if ($config !== null) {
            $objectives[] = new ilSetupConfigStoredObjective($config);
        }

        return new Setup\ObjectiveCollection(
            "Complete common ILIAS objectives.",
            false,
            ...$objectives
        );
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
        return new ilSetupMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null) : array
    {
        return [
            "registerNICKey" => new Setup\ObjectiveConstructor(
                "Register NIC key",
                static function () use ($config) : Setup\Objective {
                    if (is_null($config)) {
                        throw new \RuntimeException(
                            "Missing Config for objective 'registerNICKey'."
                        );
                    }

                    return new ilNICKeyRegisteredObjective($config);
                }
            )
        ];
    }
}
