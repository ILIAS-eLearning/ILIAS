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
use ILIAS\Setup\Objective;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Config;
use ILIAS\Setup\Migration;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Condition\PHPExtensionLoadedCondition;

class ilFileServicesSetupAgent extends Agent\NullAgent implements Agent
{
    public function getInstallObjective(Config $config = null): Objective
    {
        return new ObjectiveCollection(
            "Check for several PHP-Extensions needed by FileServices.",
            true,
            new PHPExtensionLoadedCondition("gd"),
            new PHPExtensionLoadedCondition("imagick"),
            new PHPExtensionLoadedCondition("zip")
        );
    }

    public function getUpdateObjective(Config $config = null): Objective
    {
        return $this->getInstallObjective($config);
    }
}
