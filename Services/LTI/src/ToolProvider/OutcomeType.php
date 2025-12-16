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

namespace ILIAS\LTI\ToolProvider;

enum OutcomeType: string
{
    /**
     * Decimal outcome type.
     */
    case Decimal = 'decimal';

    /**
     * Percentage outcome type.
     */
    case Percentage = 'percentage';

    /**
     * Ratio outcome type.
     */
    case Ratio = 'ratio';

    /**
     * Letter (A-F) outcome type.
     */
    case LetterAF = 'letteraf';

    /**
     * Letter (A-F) with optional +/- outcome type.
     */
    case LetterAFPlus = 'letterafplus';

    /**
     * Pass/fail outcome type.
     */
    case PassFail = 'passfail';

    /**
     * Free text outcome type.
     */
    case Text = 'freetext';

}
