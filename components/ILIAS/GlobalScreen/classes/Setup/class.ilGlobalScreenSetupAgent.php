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
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ilGlobalScreenSetupAgent implements Agent
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
        return false;
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(?Config $config = null): Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getBuildObjective(): Objective
    {
        return new ilGlobalScreenBuildProviderMapObjective();
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
}
