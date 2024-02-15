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
 ********************************************************************
 */

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;
use ILIAS\Component\Resource\PublicAssetManager;
use ILIAS\Component\Setup\PublicAssetsBuildObjective;

class ilComponentsSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    public function __construct(
        protected PublicAssetManager $public_asset_manager,
        protected array $public_assets
    ) {
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ilComponentDefinitionsStoredObjective();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Updates of Services/Components",
            false,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilIntroduceComponentArtifactDBUpdateSteps()
            ),
            new ilComponentDefinitionsStoredObjective(false)
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Build Objectives of \\ILIAS\\Component",
            false,
            new Setup\ObjectiveCollection(
                "Artifacts for \\ILIAS\\Component",
                false,
                new ilComponentBuildComponentInfoObjective(),
                new ilComponentBuildPluginInfoObjective()
            ),
            new PublicAssetsBuildObjective(
                $this->public_asset_manager,
                $this->public_assets
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
