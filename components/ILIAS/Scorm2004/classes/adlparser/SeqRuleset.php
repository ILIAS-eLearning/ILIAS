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

define("RULE_TYPE_ANY", 1);
define("RULE_TYPE_EXIT", 2);
define("RULE_TYPE_POST", 3);
define("RULE_TYPE_SKIPPED", 4);
define("RULE_TYPE_DISABLED", 5);
define("RULE_TYPE_HIDDEN", 6);
define("RULE_TYPE_FORWARDBLOCK", 7);

class SeqRuleset
{
    public array $mRules;

    public function __construct(array $iRules)
    {
        $this->mRules = $iRules;
    }
}
