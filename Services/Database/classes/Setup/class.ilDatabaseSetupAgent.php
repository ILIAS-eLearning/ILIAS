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
 
use ILIAS\Setup;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ilDatabaseSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery $refinery;

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
    public function getArrayToConfigTransformation() : Transformation
    {
        // TODO: Migrate this to refinery-methods once possible.
        return $this->refinery->custom()->transformation(function ($data) : \ilDatabaseSetupConfig {
            $data["password"] = $data["password"] ?? null; // password can be empty
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
        if (!$config instanceof \ilDatabaseSetupConfig) {
            return new Setup\Objective\NullObjective();
        }
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services\Database",
            false,
            new ilDatabaseConfigStoredObjective($config),
            new \ilDatabasePopulatedObjective($config),
            new \ilDatabaseUpdatedObjective()
        );
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        $p = [];
        if ($config instanceof ilDatabaseSetupConfig) {
            $p[] = new \ilDatabaseConfigStoredObjective($config);
        }
        $p[] = new \ilDatabaseUpdatedObjective();
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services\Database",
            false,
            ...$p
        );
    }

    /**
     * @inheritdocs
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
        return new ilDatabaseMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [
            new Setup\ilMysqlMyIsamToInnoDbMigration()
        ];
    }
}
