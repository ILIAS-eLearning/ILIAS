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

namespace ILIAS\Test\Logging;

enum TestParticipantInteractionTypes: string
{
    case WRONG_TEST_PASSWORD_PROVIDED = 'wrong_test_password_provided';
    case TEST_RUN_STARTED = 'test_run_started';
    case QUESTION_SHOWN = 'question_shown';
    case QUESTION_SKIPPED = 'question_skipped';
    case QUESTION_SUBMITTED = 'question_submitted';
    case TEST_RUN_FINISHED = 'test_run_finished';
}
