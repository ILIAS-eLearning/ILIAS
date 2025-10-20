<?php
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
