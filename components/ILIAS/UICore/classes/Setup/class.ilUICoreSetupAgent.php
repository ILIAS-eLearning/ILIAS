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

use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveConstructor;

/**
 * Class ilUICoreSetupAgent
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilUICoreSetupAgent implements Agent
{
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
    public function getInstallObjective(?Config $config = null): Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilCtrlDatabaseUpdateSteps()
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Objective
    {
        return new ObjectiveCollection(
            'buildIlCtrlArtifacts',
            false,
            new ilCtrlBaseClassArtifactObjective(),
            new ilCtrlStructureArtifactObjective(),
            new ilCtrlSecurityArtifactObjective(),
        );
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Storage $storage): Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getNamedObjectives(?Config $config = null): array
    {
        return [
            'buildIlCtrlArtifacts' => new ObjectiveConstructor(
                'builds all necessary ilCtrl artifacts.',
                function () {
                    return $this->getBuildObjective();
                }
            ),

            'updateIlCtrlDatabase' => new ObjectiveConstructor(
                'executes all ilCtrl database update steps.',
                function () {
                    return $this->getUpdateObjective();
                }
            ),
        ];
    }
}
