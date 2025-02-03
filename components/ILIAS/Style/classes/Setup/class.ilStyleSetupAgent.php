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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilStyleSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @var Refinery\Factory
     */
    protected $refinery;

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
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilStyleSetupConfig(
                $data["manage_system_styles"] ?? false,
                $data["path_to_scss"] ?? null
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new ilStyleConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        if ($config !== null) {
            return new ilStyleConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new ilKitchenSinkDataCollectedObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilStyleMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [
            new ilStyleIRSSMigration()
        ];
    }

    public function getNamedObjectives(?Setup\Config $config = null): array
    {
        return [
            'buildUIFrameworkExampleTestCases.update' => new Setup\ObjectiveConstructor(
                'builds an import file for testrail for all KNOWN ids.',
                fn() => new ilUITestRailExampleTestCasesObjective(false)
            ),

            'buildUIFrameworkExampleTestCases.new' => new Setup\ObjectiveConstructor(
                'builds an import file for testrail with ONLY NEW cases.',
                fn() => new ilUITestRailExampleTestCasesObjective(true)
            ),


        ];
    }
}
