<?php

declare(strict_types=1);

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

namespace ILIAS\Setup\Condition;

use ILIAS\Setup;

class PHPExtensionLoadedCondition extends ExternalConditionObjective
{
    public function __construct(string $which)
    {
        $ilias_version = ILIAS_VERSION_NUMERIC;

        return parent::__construct(
            "PHP extension \"$which\" loaded",
            fn (Setup\Environment $env): bool => in_array($which, get_loaded_extensions()),
            "ILIAS $ilias_version requires the PHP extension $which."
        );
    }
}
