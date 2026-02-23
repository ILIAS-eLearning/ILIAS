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

namespace ILIAS\StaticURL;

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\StaticURL\Setup\Shortlinks\ShortlinksDBSteps11;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class SetupAgent extends NullAgent implements Agent
{
    #[\Override]
    public function getBuildObjective(): Objective
    {
        return new ArtifactObjective();
    }

    #[\Override]
    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Static URL Services',
            true,
            new \ilTreeAdminNodeAddedObjective(
                'stus',
                '__StaticURLServiceAdministration'
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new ShortlinksDBSteps11()
            )
        );
    }

}
