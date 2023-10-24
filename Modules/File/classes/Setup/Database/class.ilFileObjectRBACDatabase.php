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

use ILIAS\Setup\ObjectiveConstructor;
use ILIAS\Setup\Config;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery;
use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilFileObjectRBACDatabase extends ilDatabaseUpdateStepsExecutedObjective
{
    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            parent::getPreconditions($environment),
            [
                new ilAccessCustomRBACOperationAddedObjective(
                    ilFileObjectRBACDatabaseSteps::EDIT_FILE,
                    "Edit File",
                    "object",
                    5990,
                    ["file"]
                )
            ]
        );
    }

}
