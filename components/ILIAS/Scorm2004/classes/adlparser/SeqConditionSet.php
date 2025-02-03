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

define("EVALUATE_UNKNOWN", 0);
define("EVALUATE_TRUE", 1);
define("EVALUATE_FALSE", -1);
define("COMBINATION_ALL", "all");
define("COMBINATION_ANY", "any");

class SeqConditionSet
{
    public ?string $mCombination = null;

    //convert vector to array
    public ?array $mConditions = null;
    public bool $mRetry = false;
    public bool $mRollup = false;

    public function __construct(bool $iRollup)
    {
        $this->mRollup = $iRollup;
    }
}
