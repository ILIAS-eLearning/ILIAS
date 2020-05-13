<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ilDatabaseSetupAgent implements Setup\Agent
{
    /**
     * @var Refinery
     */
    protected $refinery;

    public function __construct(Refinery $refinery)
    {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdocs
     */
    public function hasConfig() : bool
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function getConfigInput(Setup\Config $config = null) : ILIAS\UI\Component\Input\Field\Input
    {
        throw new \LogicException("NYI!");
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        // TODO: Migrate this to refinery-methods once possible.
        return $this->refinery->custom()->transformation(function ($data) {
            $password = $this->refinery->to()->data("password");
            return new \ilDatabaseSetupConfig(
                $data["type"] ?? "innodb",
                $data["host"] ?? "localhost",
                $data["database"] ?? "ilias",
                $data["user"] ?? null,
                $data["password"] ? $password->transform($data["password"]) : null,
                $data["create_database"] ?? true,
                $data["collation"] ?? null,
                $data["port"] ?? 3306,
                $data["path_to_db_dump"] ?? null
            );
        });
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services\Database",
            false,
            new ilDatabaseConfigStoredObjective($config),
            new \ilDatabaseUpdatedObjective($config)
        );
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new \ilDatabaseUpdatedObjective($config, false);
    }

    /**
     * @inheritdocs
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }
}
