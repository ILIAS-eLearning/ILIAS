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

define("ROLLUP_ACTION_NOCHANGE", 0);
define("ROLLUP_ACTION_SATISFIED", 1);
define("ROLLUP_ACTION_NOTSATISFIED", 2);
define("ROLLUP_ACTION_COMPLETED", 3);
define("ROLLUP_ACTION_INCOMPLETE", 4);

define("ROLLUP_CONSIDER_ALWAYS", "always");
define("ROLLUP_CONSIDER_ATTEMPTED", "ifAttempted");
define("ROLLUP_CONSIDER_NOTSKIPPED", "ifNotSkipped");
define("ROLLUP_CONSIDER_NOTSUSPENDED", "ifNotSuspended");

define("ROLLUP_SET_ALL", "all");
define("ROLLUP_SET_ANY", "any");
define("ROLLUP_SET_NONE", "none");
define("ROLLUP_SET_ATLEASTCOUNT", "atLeastCount");
define("ROLLUP_SET_ATLEASTPERCENT", "atLeastPercent");

class SeqRollupRule
{
    public int $mAction = ROLLUP_ACTION_SATISFIED;

    public string $mChildActivitySet = ROLLUP_SET_ALL;

    public int $mMinCount = 0;

    public float $mMinPercent = 0.0;

    public ?array $mConditions = null;

    public function __construct()
    {
        //$this->mRules=$iRules;
    }

    public function setRollupAction(string $iAction): void
    {
        if ($iAction === "satisfied") {
            $this->mAction = ROLLUP_ACTION_SATISFIED;
        } elseif ($iAction === "notSatisfied") {
            $this->mAction = ROLLUP_ACTION_NOTSATISFIED;
        } elseif ($iAction === "completed") {
            $this->mAction = ROLLUP_ACTION_COMPLETED;
        } elseif ($iAction === "incomplete") {
            $this->mAction = ROLLUP_ACTION_INCOMPLETE;
        }
    }
}
