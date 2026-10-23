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

use Pimple\Container;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries;
use ILIAS\LearningSequence\Content\Adaptive\LSOItemPath;
use ILIAS\LearningSequence\Content\Adaptive\LSOItemVisits;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ConditionHandler;
use ILIAS\LearningSequence\Content\Condition\ConditionTypes;

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

        $this["db.states"] = function ($c) use ($dic): ilLSStateDB {
            return new ilLSStateDB($dic["ilDB"]);
        };

        $this["db.postconditions"] = function ($c) use ($dic): ilLSPostConditionDB {
            return new ilLSPostConditionDB($dic["ilDB"]);
        };

        $this["db.boundaries"] = function ($c) use ($dic): LSOAdaptiveBoundaries {
            return new LSOAdaptiveBoundaries($dic["ilDB"]);
        };

        $this["db.item_path"] = function ($c) use ($dic): LSOItemPath {
            return new LSOItemPath($dic["ilDB"]);
        };

        $this["db.item_visits"] = function ($c) use ($dic): LSOItemVisits {
            return new LSOItemVisits($dic["ilDB"]);
        };

        $this["conditions.types"] = function ($c) use ($dic): ConditionTypes {
            return $dic["lso.condition_types"];
        };

        $this["conditions.factory"] = function ($c) use ($dic): ConditionFactory {
            return new ConditionFactory(
                $c["conditions.types"],
                $dic["ilDB"]
            );
        };

        $this["conditions.handler"] = function ($c) use ($dic): ConditionHandler {
            return new ConditionHandler(
                $dic["ilDB"],
                $dic["lng"],
                $c["conditions.types"],
                $c["conditions.factory"]
            );
        };

        $this["gs.current_context"] = function ($c) use ($dic): ScreenContext {
            return $dic->globalScreen()->tool()->context()->current();
        };
    }
}
