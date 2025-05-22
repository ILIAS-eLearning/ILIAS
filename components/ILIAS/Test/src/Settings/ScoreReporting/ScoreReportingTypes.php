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

namespace ILIAS\Test\Settings\ScoreReporting;

use ILIAS\Language\Language;

enum ScoreReportingTypes: int
{
    case SCORE_REPORTING_DISABLED = 0;
    case SCORE_REPORTING_FINISHED = 1;
    case SCORE_REPORTING_IMMIDIATLY = 2;
    case SCORE_REPORTING_DATE = 3;
    case SCORE_REPORTING_AFTER_PASSED = 4;

    public function isReportingEnabled(): bool
    {
        return match($this) {
            self::SCORE_REPORTING_DISABLED => false,
            default => true
        };
    }

    public function getTranslatedValue(Language $lng): string
    {
        return match ($this) {
            self::SCORE_REPORTING_FINISHED => $lng->txt('tst_report_after_test'),
            self::SCORE_REPORTING_IMMIDIATLY => $lng->txt('tst_report_after_first_question'),
            self::SCORE_REPORTING_DATE => $lng->txt('tst_report_after_date'),
            self::SCORE_REPORTING_AFTER_PASSED => $lng->txt('tst_report_after_passed'),
            default => $lng->txt('tst_report_never')
        };
    }
}
