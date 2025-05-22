<?php

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

declare(strict_types=1);

use ILIAS\Setup\Agent;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\ilMysqlMyIsamToInnoDbMigration;
use ILIAS\Setup\ObjectiveConstructor;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ilDatabaseSetupAgent implements Agent
{
    use HasNoNamedObjective;

    public function __construct(protected Refinery $refinery)
    {
    }

    /**
     * @inheritdocs
     */
    public function hasConfig(): bool
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation(): Transformation
    {
        // TODO: Migrate this to refinery-methods once possible.
        return $this->refinery->custom()->transformation(function ($data): \ilDatabaseSetupConfig {
            $data["password"] ??= null; // password can be empty
            $password = $this->refinery->to()->data("password");
            return new \ilDatabaseSetupConfig(
                $data["type"] ?? "innodb",
                $data["host"] ?? "localhost",
                $data["database"] ?? "ilias",
                $data["user"] ?? null,
                $data["password"] ? $password->transform($data["password"]) : null,
                $data["create_database"] ?? true,
                $data["collation"] ?? null,
                (int) ($data["port"] ?? 3306),
                $data["path_to_db_dump"] ?? null
            );
        });
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(?Config $config = null): Objective
    {
        if (!$config instanceof \ilDatabaseSetupConfig) {
            return new NullObjective();
        }
        return new ObjectiveCollection(
            "Complete objectives from Services\Database",
            false,
            new ilDatabaseConfigStoredObjective($config),
            new ilDatabaseEnvironmentValidObjective(),
            new \ilDatabaseUpdatedObjective()
        );
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(?Config $config = null): Objective
    {
        $p = [];
        $p[] = new \ilDatabaseUpdatedObjective();
        $p[] = new ilDatabaseEnvironmentValidObjective();
        return new ObjectiveCollection(
            "Complete objectives from Services\Database",
            false,
            ...$p
        );
    }

    /**
     * @inheritdocs
     */
    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Storage $storage): Objective
    {
        return new ilDatabaseMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [
            new ilMysqlMyIsamToInnoDbMigration()
        ];
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [
            'resetFailedSteps' => new ObjectiveConstructor(
                'reset null-states in il_db_steps',
                static fn(): Objective => new ilDatabaseResetStepsObjective()
            )
        ];
    }
}
