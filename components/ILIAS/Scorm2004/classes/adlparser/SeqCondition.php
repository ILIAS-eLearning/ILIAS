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

define("SATISFIED", "satisfied");
define("OBJSTATUSKNOWN", "objectiveStatusKnown");
define("OBJMEASUREKNOWN", "objectiveMeasureKnown");
define("OBJMEASUREGRTHAN", "objectiveMeasureGreaterThan");
define("OBJMEASURELSTHAN", "objectiveMeasureLessThan");
define("COMPLETED", "completed");
define("PROGRESSKNOWN", "activityProgressKnown");
define("ATTEMPTED", "attempted");
define("ATTEMPTSEXCEEDED", "attemptLimitExceeded");
define("TIMELIMITEXCEEDED", "timeLimitExceeded");
define("OUTSIDETIME", "outsideAvailableTimeRange");
define("ALWAYS", "always");
define("NEVER", "never");

class SeqCondition
{
    public ?string $mCondition = null;
    public bool $mNot = false;
    public ?string $mObjID = null;
    public float $mThreshold = 0.0;

    public function __construct()
    {
    }
}
