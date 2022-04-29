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
 
namespace ILIAS\Setup\Agent;

use ILIAS\Setup\Agent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
 * An agent that just doesn't do a thing.
 */
class NullAgent implements Agent
{
    protected Refinery $refinery;

    public function __construct(
        Refinery $refinery
    ) {
        $this->refinery = $refinery;
    }

    /**
      * @inheritdocs
      */
    public function hasConfig() : bool
    {
        return false;
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException(
            self::class . " has no config."
        );
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(Config $config = null) : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(Config $config = null) : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getBuildArtifactObjective() : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new NullObjective();
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
        return [];
    }
}
