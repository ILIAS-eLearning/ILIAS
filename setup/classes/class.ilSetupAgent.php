<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

/**
 * Contains common objectives for the setup. Do not make additions here, in
 * general all this stuff here is supposed to go elsewhere once we find out
 * which service it really belongs to.
 */
class ilSetupAgent implements Setup\Agent
{
    const PHP_MEMORY_LIMIT = "128M";

    /**
     * @var Refinery\Factory
     */
    protected $refinery;

    /**
     * @var	Data\Factory
     */
    protected $data;

    /**
     * @var \ilSetupPasswordManager
     */
    protected $password_manager;

    public function __construct(
        Refinery\Factory $refinery,
        Data\Factory $data,
        \ilSetupPasswordManager $password_manager
    ) {
        $this->refinery = $refinery;
        $this->data = $data;
        $this->password_manager = $password_manager;
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
    public function getConfigInput(Setup\Config $config = null) : UI\Component\Input\Field\Input
    {
        throw new \LogicException("Not yet implemented.");
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            $password = $this->refinery->to()->data("password");
            $datetimezone = $this->refinery->to()->toNew(\DateTimeZone::class);
            return new \ilSetupConfig(
                $data["client_id"],
                $password->transform($data["master_password"]),
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
                new ilSetupConfigStoredObjective($config, $this->password_manager),
                $config->getRegisterNIC()
                        ? new ilNICKeyRegisteredObjective($config)
                        : new ilNICKeyStoredObjective($config)
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
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }
}
