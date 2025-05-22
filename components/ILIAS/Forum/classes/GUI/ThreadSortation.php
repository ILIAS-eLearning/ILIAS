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

enum ThreadSortation: int
{
    public const DEFAULT_SORTATION = self::LAST_POST_DESC;

    case SUBJECT_ASC = 1;
    case SUBJECT_DESC = 2;
    case LAST_POST_ASC = 3;
    case LAST_POST_DESC = 4;
    case RATING_ASC = 5;
    case RATING_DESC = 6;

    public function languageId(): string
    {
        return match($this) {
            self::SUBJECT_ASC => 'forums_thread_sorting_asc',
            self::SUBJECT_DESC => 'forums_thread_sorting_dsc',
            self::LAST_POST_ASC => 'forums_last_posting_asc',
            self::LAST_POST_DESC => 'forums_last_posting_dsc',
            self::RATING_ASC => 'forums_rating_asc',
            self::RATING_DESC => 'forums_rating_dsc',
        };
    }

    public function field(): string
    {
        return match($this) {
            self::SUBJECT_ASC, self::SUBJECT_DESC => 'thr_subject',
            self::LAST_POST_ASC, self::LAST_POST_DESC => 'lp_date',
            self::RATING_ASC, self::RATING_DESC => 'rating',
        };
    }

    public function direction(): string
    {
        return match($this) {
            self::SUBJECT_ASC, self::LAST_POST_ASC, self::RATING_ASC => 'asc',
            self::SUBJECT_DESC, self::LAST_POST_DESC, self::RATING_DESC => 'desc',
        };
    }
}
