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

define("SEQ_ACTION_NOACTION", "noaction");
define("SEQ_ACTION_IGNORE", "ignore");
define("SEQ_ACTION_SKIP", "skip");
define("SEQ_ACTION_DISABLED", "disabled");
define("SEQ_ACTION_HIDEFROMCHOICE", "hiddenFromChoice");
define("SEQ_ACTION_FORWARDBLOCK", "stopForwardTraversal");
define("SEQ_ACTION_EXITPARENT", "exitParent");
define("SEQ_ACTION_EXITALL", "exitAll");
define("SEQ_ACTION_RETRY", "retry");
define("SEQ_ACTION_RETRYALL", "retryAll");
define("SEQ_ACTION_CONTINUE", "continue");
define("SEQ_ACTION_PREVIOUS", "previous");
define("SEQ_ACTION_EXIT", "exit");


class SeqRule
{
    public string $mAction = SEQ_ACTION_IGNORE;
    public ?array $mConditions = null;

    public function __construct()
    {
    }
}
