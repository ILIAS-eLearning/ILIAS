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

use Pimple\Container;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

class ilLSDI extends Container
{
    public function init(ArrayAccess $dic): void
    {
        $this["db.filesystem"] = function ($c): ilLearningSequenceFilesystem {
            return new ilLearningSequenceFilesystem();
        };

        $this["db.settings"] = function ($c) use ($dic): ilLearningSequenceSettingsDB {
            return new ilLearningSequenceSettingsDB(
                $dic["ilDB"],
                $c["db.filesystem"]
            );
        };

        $this["db.activation"] = function ($c) use ($dic): ilLearningSequenceActivationDB {
            return new ilLearningSequenceActivationDB($dic["ilDB"]);
        };

        $this["db.states"] = function ($c) use ($dic): ilLSStateDB {
            return new ilLSStateDB($dic["ilDB"]);
        };

        $this["db.postconditions"] = function ($c) use ($dic): ilLSPostConditionDB {
            return new ilLSPostConditionDB($dic["ilDB"]);
        };

        $this["gs.current_context"] = function ($c) use ($dic): ScreenContext {
            return $dic->globalScreen()->tool()->context()->current();
        };
    }
}
